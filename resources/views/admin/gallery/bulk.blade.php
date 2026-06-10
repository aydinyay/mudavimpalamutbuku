@extends('layouts.admin')

@section('title', 'Toplu Fotoğraf Yükle')
@section('page_title', 'Toplu Fotoğraf Yükle')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Birden fazla fotoğrafı aynı anda seçip yükleyin.</p>
    <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
        <i class="bi bi-arrow-left me-1"></i> Galeriye Dön
    </a>
</div>

{{-- Drop zone --}}
<div id="dropZone"
     style="border:2.5px dashed #1a7fa8;border-radius:14px;background:#f0f8ff;
            padding:40px 20px;text-align:center;cursor:pointer;transition:background .2s;">
    <i class="bi bi-cloud-upload" style="font-size:2.8rem;color:#1a7fa8;display:block;margin-bottom:10px;"></i>
    <p class="mb-1 fw-600" style="color:#1a7fa8;">Fotoğrafları buraya sürükleyin</p>
    <p class="text-muted small mb-3">veya</p>
    <label class="btn px-4" style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:8px;cursor:pointer;">
        <i class="bi bi-images me-1"></i> Fotoğraf Seç
        <input type="file" id="fileInput" multiple accept="image/jpeg,image/jpg,image/png,image/webp"
               style="display:none;">
    </label>
    <p class="text-muted mt-3 mb-0" style="font-size:.8rem;">
        JPEG · PNG · WebP &nbsp;·&nbsp; Maks. 8 MB / fotoğraf &nbsp;·&nbsp; Otomatik 1200×900 WebP'ye dönüştürülür
    </p>
</div>

{{-- Seçilen dosyalar önizleme + yükleme kuyruğu --}}
<div id="queueSection" style="display:none;" class="mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span id="queueSummary" class="fw-600 text-muted"></span>
        <div class="d-flex gap-2">
            <button id="addMoreBtn" class="btn btn-outline-secondary btn-sm" style="border-radius:8px;">
                <i class="bi bi-plus"></i> Daha Fazla Ekle
            </button>
            <button id="uploadBtn" class="btn btn-sm px-4"
                    style="background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border-radius:8px;">
                <i class="bi bi-upload me-1"></i> Hepsini Yükle
            </button>
        </div>
    </div>

    <div id="previewGrid" class="row g-2"></div>

    {{-- Genel ilerleme --}}
    <div id="overallProgress" style="display:none;" class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-600" id="progressLabel">Yükleniyor...</span>
            <span class="small text-muted" id="progressCount"></span>
        </div>
        <div class="progress" style="height:10px;border-radius:10px;">
            <div id="progressBar" class="progress-bar" role="progressbar"
                 style="width:0%;background:linear-gradient(90deg,#1a7fa8,#2ea887);border-radius:10px;transition:width .3s;"></div>
        </div>
    </div>

    {{-- Tamamlandı banner --}}
    <div id="doneBanner" style="display:none;" class="alert mt-4 text-center"
         style="border-radius:10px;background:linear-gradient(135deg,#1a7fa8,#2ea887);color:#fff;border:none;">
        <i class="bi bi-check-circle-fill me-2" style="font-size:1.3rem;"></i>
        <span id="doneText" class="fw-600"></span>
        <div class="mt-3">
            <a href="{{ route('admin.gallery.index') }}" class="btn btn-light btn-sm me-2" style="border-radius:8px;">
                <i class="bi bi-images me-1"></i> Galeriyi Görüntüle
            </a>
            <button id="uploadMoreBtn" class="btn btn-outline-light btn-sm" style="border-radius:8px;">
                <i class="bi bi-plus me-1"></i> Daha Fazla Yükle
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    var UPLOAD_URL = '{{ route("admin.gallery.bulk.store") }}';
    var CSRF       = document.querySelector('meta[name="csrf-token"]').content;

    var files      = [];  // {file, state: 'pending'|'uploading'|'done'|'error'}
    var uploading  = false;

    var dropZone       = document.getElementById('dropZone');
    var fileInput      = document.getElementById('fileInput');
    var queueSection   = document.getElementById('queueSection');
    var previewGrid    = document.getElementById('previewGrid');
    var uploadBtn      = document.getElementById('uploadBtn');
    var addMoreBtn     = document.getElementById('addMoreBtn');
    var uploadMoreBtn  = document.getElementById('uploadMoreBtn');
    var queueSummary   = document.getElementById('queueSummary');
    var overallProg    = document.getElementById('overallProgress');
    var progressBar    = document.getElementById('progressBar');
    var progressLabel  = document.getElementById('progressLabel');
    var progressCount  = document.getElementById('progressCount');
    var doneBanner     = document.getElementById('doneBanner');
    var doneText       = document.getElementById('doneText');

    // Drop zone tıklama
    dropZone.addEventListener('click', function () { fileInput.click(); });
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.style.background = '#d0ecf8';
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.style.background = '#f0f8ff';
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.style.background = '#f0f8ff';
        addFiles(Array.from(e.dataTransfer.files));
    });

    fileInput.addEventListener('change', function () {
        addFiles(Array.from(fileInput.files));
        fileInput.value = '';
    });

    addMoreBtn.addEventListener('click', function () { fileInput.click(); });
    uploadMoreBtn.addEventListener('click', function () {
        doneBanner.style.display = 'none';
        previewGrid.innerHTML = '';
        files = [];
        updateSummary();
        fileInput.click();
    });

    uploadBtn.addEventListener('click', startUpload);

    function addFiles(newFiles) {
        var allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        newFiles.forEach(function (f) {
            if (!allowed.includes(f.type)) return;
            if (f.size > 8 * 1024 * 1024) {
                alert(f.name + ' çok büyük (maks. 8 MB).');
                return;
            }
            var idx = files.length;
            files.push({ file: f, state: 'pending', idx: idx });
            renderCard(files[idx]);
        });
        if (files.length > 0) {
            queueSection.style.display = '';
            doneBanner.style.display = 'none';
        }
        updateSummary();
    }

    function renderCard(item) {
        var card = document.createElement('div');
        card.className = 'col-6 col-sm-4 col-md-3 col-lg-2';
        card.id = 'card-' + item.idx;
        card.innerHTML = [
            '<div style="border-radius:10px;overflow:hidden;background:#f0f0f0;aspect-ratio:1;position:relative;">',
            '  <img id="img-' + item.idx + '" style="width:100%;height:100%;object-fit:cover;" alt="">',
            '  <div id="overlay-' + item.idx + '" style="position:absolute;inset:0;display:flex;flex-direction:column;',
            '       align-items:center;justify-content:center;background:rgba(0,0,0,.45);opacity:0;transition:opacity .2s;">',
            '    <i id="icon-' + item.idx + '" class="bi bi-clock" style="font-size:1.6rem;color:#fff;"></i>',
            '    <span id="label-' + item.idx + '" style="color:#fff;font-size:.7rem;margin-top:4px;">Bekliyor</span>',
            '  </div>',
            '</div>',
            '<p style="font-size:.65rem;margin:4px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#666;" title="' + item.file.name + '">' + item.file.name + '</p>',
        ].join('');
        previewGrid.appendChild(card);

        // Önizleme
        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('img-' + item.idx).src = e.target.result;
            document.getElementById('overlay-' + item.idx).style.opacity = '1';
        };
        reader.readAsDataURL(item.file);
    }

    function setCardState(item, state) {
        item.state = state;
        var overlay = document.getElementById('overlay-' + item.idx);
        var icon    = document.getElementById('icon-' + item.idx);
        var label   = document.getElementById('label-' + item.idx);
        if (!overlay) return;
        overlay.style.opacity = '1';
        if (state === 'uploading') {
            overlay.style.background = 'rgba(26,127,168,.75)';
            icon.className  = 'bi bi-arrow-repeat spin';
            icon.style.color = '#fff';
            label.textContent = 'Yükleniyor...';
        } else if (state === 'done') {
            overlay.style.background = 'rgba(46,168,135,.85)';
            icon.className  = 'bi bi-check-circle-fill';
            icon.style.color = '#fff';
            label.textContent = 'Tamam';
        } else if (state === 'error') {
            overlay.style.background = 'rgba(220,38,38,.75)';
            icon.className  = 'bi bi-x-circle-fill';
            icon.style.color = '#fff';
            label.textContent = 'Hata';
        } else {
            overlay.style.background = 'rgba(0,0,0,.45)';
            icon.className  = 'bi bi-clock';
            label.textContent = 'Bekliyor';
        }
    }

    function updateSummary() {
        var pending = files.filter(function (f) { return f.state === 'pending'; }).length;
        var done    = files.filter(function (f) { return f.state === 'done'; }).length;
        queueSummary.textContent = files.length + ' fotoğraf seçildi' + (done ? ' · ' + done + ' yüklendi' : '');
        uploadBtn.disabled = (pending === 0 || uploading);
    }

    function startUpload() {
        if (uploading) return;
        uploading = true;
        uploadBtn.disabled = true;
        addMoreBtn.disabled = true;
        overallProg.style.display = '';
        doneBanner.style.display  = 'none';

        var pending = files.filter(function (f) { return f.state === 'pending'; });
        var total   = pending.length;
        var done    = 0;
        var errors  = 0;

        function next() {
            if (pending.length === 0) {
                uploading = false;
                addMoreBtn.disabled = false;
                progressBar.style.width = '100%';
                progressLabel.textContent = 'Tamamlandı!';
                progressCount.textContent = done + '/' + total;
                doneBanner.style.display = '';
                doneText.textContent = done + ' fotoğraf yüklendi' + (errors ? ', ' + errors + ' hata' : '') + '.';
                updateSummary();
                return;
            }

            var item = pending.shift();
            setCardState(item, 'uploading');

            var fd = new FormData();
            fd.append('photo', item.file);
            fd.append('_token', CSRF);

            fetch(UPLOAD_URL, { method: 'POST', body: fd })
                .then(function (r) {
                    if (!r.ok) throw new Error(r.status);
                    return r.json();
                })
                .then(function (d) {
                    if (d.ok) {
                        setCardState(item, 'done');
                        done++;
                    } else {
                        setCardState(item, 'error');
                        errors++;
                    }
                })
                .catch(function () {
                    setCardState(item, 'error');
                    errors++;
                })
                .finally(function () {
                    var completed = done + errors;
                    progressBar.style.width = Math.round(completed / total * 100) + '%';
                    progressLabel.textContent = 'Yükleniyor... (' + completed + '/' + total + ')';
                    progressCount.textContent = completed + '/' + total;
                    next();
                });
        }

        next();
    }
}());
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { display:inline-block; animation: spin .8s linear infinite; }
</style>
@endpush

@endsection
