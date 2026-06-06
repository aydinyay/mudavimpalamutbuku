@props(['context' => 'website'])

<style>
#sp-ambient {
    position: fixed;
    top: 90px;
    right: 20px;
    z-index: 1040;
    text-align: right;
    display: none;
    cursor: pointer;
    user-select: none;
}
#sp-ambient-text {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    color: #fff;
    opacity: 0.5;
    transition: opacity .3s;
    line-height: 1.4;
    text-shadow: 0 1px 8px rgba(0,0,0,.4);
}
#sp-ambient:hover #sp-ambient-text { opacity: 0.82; }
#sp-ambient-label {
    font-size: .72rem;
    letter-spacing: .06em;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 5px;
}
#sp-ambient-song  { font-size: 1.08rem; font-weight: 600; }
#sp-ambient-artist{ font-size: .78rem; font-weight: 400; }

/* Expanded detail card */
#sp-detail {
    display: none;
    position: fixed;
    top: 162px;
    right: 20px;
    z-index: 1041;
    background: rgba(18,16,16,.93);
    backdrop-filter: blur(14px);
    border-radius: 14px;
    padding: 14px;
    width: 230px;
    border: 1px solid rgba(255,255,255,.1);
    box-shadow: 0 8px 32px rgba(0,0,0,.5);
    color: #fff;
}
#sp-detail-close {
    position: absolute;
    top: 8px; right: 10px;
    background: none; border: none;
    color: #666; font-size: .8rem;
    cursor: pointer; padding: 0;
}
</style>

{{-- Ambient text widget --}}
<div id="sp-ambient" onclick="spToggleDetail()">
    <div id="sp-ambient-text">
        <div id="sp-ambient-label">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="opacity:.7;flex-shrink:0;">
                <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
            </svg>
            <span id="sp-ambient-label-text">Müdavimde şu an çalan tınılar</span>
        </div>
        <div id="sp-ambient-song"></div>
        <div id="sp-ambient-artist"></div>
    </div>
</div>

{{-- Detail card (click to open) --}}
<div id="sp-detail">
    <button id="sp-detail-close" onclick="event.stopPropagation();spCloseDetail()" title="Kapat">✕</button>

    <div id="sp-detail-now" style="display:none;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <img id="sp-detail-cover" src="" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;flex-shrink:0;">
            <div style="overflow:hidden;flex:1;">
                <a id="sp-detail-name" href="#" target="_blank"
                   style="font-family:'Cormorant Garamond',serif;font-size:.92rem;font-weight:600;font-style:italic;color:#fff;text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></a>
                <div id="sp-detail-artist" style="font-size:.68rem;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
            </div>
        </div>
        <div style="background:rgba(255,255,255,.12);border-radius:3px;height:3px;margin-bottom:12px;">
            <div id="sp-detail-progress" style="background:#1DB954;height:3px;border-radius:3px;width:0%;transition:width .5s linear;"></div>
        </div>
    </div>

    <div id="sp-detail-recent" style="display:none;">
        <div style="font-size:.56rem;font-weight:700;letter-spacing:.1em;color:#666;margin-bottom:6px;">AZ ÖNCE</div>
        <div id="sp-detail-recent-list"></div>
    </div>

    <div id="sp-detail-queue" style="display:none;margin-top:8px;">
        <div style="font-size:.56rem;font-weight:700;letter-spacing:.1em;color:#666;margin-bottom:6px;">SIRADA</div>
        <div id="sp-detail-queue-list"></div>
    </div>

    <div id="sp-detail-fallback" style="display:none;text-align:center;padding:4px 0 2px;">
        <div style="font-size:.65rem;color:#666;margin-bottom:5px;font-style:italic;">Şu an çalmıyor</div>
        <a id="sp-detail-fb-link" href="#" target="_blank"
           style="font-size:.72rem;color:#1DB954;text-decoration:none;font-weight:600;">
            🎵 <span id="sp-detail-fb-name">Playlist</span>
        </a>
    </div>
</div>

<script>
(function(){
    var CTX     = '{{ $context }}';
    var ambient = document.getElementById('sp-ambient');
    var detail  = document.getElementById('sp-detail');
    var open    = false;

    function spToggleDetail(){ open ? spCloseDetail() : spOpenDetail(); }
    function spOpenDetail()  { open=true;  detail.style.display='block'; }
    function spCloseDetail() { open=false; detail.style.display='none';  }
    window.spToggleDetail = spToggleDetail;
    window.spCloseDetail  = spCloseDetail;

    document.addEventListener('click', function(e){
        if (open && !detail.contains(e.target) && !ambient.contains(e.target)) {
            spCloseDetail();
        }
    });

    function trackRow(t){
        return '<div style="display:flex;align-items:center;gap:7px;margin-bottom:4px;">'
            +(t.cover ? '<img src="'+t.cover+'" style="width:26px;height:26px;border-radius:4px;object-fit:cover;flex-shrink:0;">'
                      : '<div style="width:26px;height:26px;border-radius:4px;background:#2a2a2a;flex-shrink:0;"></div>')
            +'<div style="overflow:hidden;">'
            +'<div style="font-size:.7rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+t.name+'</div>'
            +'<div style="font-size:.6rem;color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+t.artists+'</div>'
            +'</div></div>';
    }

    function applyData(d){
        var key = CTX === 'menu' ? 'widget_menu' : 'widget_website';
        if (!d[key]) { ambient.style.display='none'; return; }

        if (d.now_playing) {
            var np = d.now_playing;
            ambient.style.display = 'block';
            document.getElementById('sp-ambient-label-text').textContent = 'Müdavimde şu an çalan tınılar';
            document.getElementById('sp-ambient-song').textContent   = np.name;
            document.getElementById('sp-ambient-artist').textContent = '— ' + np.artists;

            // Detail card — now playing
            document.getElementById('sp-detail-now').style.display      = 'block';
            document.getElementById('sp-detail-fallback').style.display  = 'none';
            document.getElementById('sp-detail-cover').src               = np.cover || '';
            document.getElementById('sp-detail-name').textContent        = np.name;
            document.getElementById('sp-detail-name').href               = np.spotify_url || '#';
            document.getElementById('sp-detail-artist').textContent      = np.artists;
            var pct = np.duration_ms > 0 ? Math.round(np.progress_ms / np.duration_ms * 100) : 0;
            document.getElementById('sp-detail-progress').style.width   = pct + '%';
        } else if (d.fallback && d.fallback.name) {
            ambient.style.display = 'block';
            document.getElementById('sp-ambient-label-text').textContent = 'Müdavimde çalan tınılar';
            document.getElementById('sp-ambient-song').textContent   = d.fallback.name;
            document.getElementById('sp-ambient-artist').textContent = '';

            document.getElementById('sp-detail-now').style.display     = 'none';
            document.getElementById('sp-detail-fallback').style.display = 'block';
            document.getElementById('sp-detail-fb-name').textContent   = d.fallback.name;
            document.getElementById('sp-detail-fb-link').href          = d.fallback.url || '#';
        } else {
            ambient.style.display = 'none';
        }

        // Recent
        var recentWrap = document.getElementById('sp-detail-recent');
        var recentList = document.getElementById('sp-detail-recent-list');
        if (d.recent && d.recent.length > 0) {
            recentWrap.style.display = 'block';
            recentList.innerHTML = d.recent.slice(0,3).map(trackRow).join('');
        } else {
            recentWrap.style.display = 'none';
        }

        // Queue
        var queueWrap = document.getElementById('sp-detail-queue');
        var queueList = document.getElementById('sp-detail-queue-list');
        if (d.queue && d.queue.length > 0) {
            queueWrap.style.display = 'block';
            queueList.innerHTML = d.queue.map(trackRow).join('');
        } else {
            queueWrap.style.display = 'none';
        }
    }

    function poll(){
        fetch('/spotify/status')
            .then(function(r){ return r.json(); })
            .then(applyData)
            .catch(function(){});
    }

    poll();
    setInterval(poll, 30000);
})();
</script>
