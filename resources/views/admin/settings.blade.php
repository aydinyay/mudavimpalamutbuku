@extends('layouts.admin')

@section('title', 'Ayarlar')
@section('page_title', 'Restoran Ayarları')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong>İletişim</strong></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Telefon</label>
                            <input type="text" name="phone" class="form-control" value="{{ $settings->phone }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">WhatsApp (+90...)</label>
                            <input type="text" name="whatsapp" class="form-control" value="{{ $settings->whatsapp }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ $settings->instagram_url }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ $settings->facebook_url }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong>Sezon Ayarları</strong></div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label">Sezon Açılış (AA-GG)</label>
                            <input type="text" name="season_open_date" class="form-control"
                                   value="{{ $settings->season_open_date }}" placeholder="05-01">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Sezon Kapanış (AA-GG)</label>
                            <input type="text" name="season_close_date" class="form-control"
                                   value="{{ $settings->season_close_date }}" placeholder="10-31">
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label">Açılış Saati</label>
                            <input type="time" name="open_time" class="form-control"
                                   value="{{ $settings->open_time ?? '09:00' }}">
                            <div class="form-text">Ör: 09:00</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label">Kapanış Saati</label>
                            <input type="time" name="close_time" class="form-control"
                                   value="{{ $settings->close_time ?? '02:00' }}">
                            <div class="form-text">Gece için ör: 02:00</div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Zorla Aç/Kapat</label>
                            <select name="is_open_override" class="form-select">
                                <option value="">Otomatik (sezona göre)</option>
                                <option value="1" @selected($settings->is_open_override === true)>Her zaman açık</option>
                                <option value="0" @selected($settings->is_open_override === false)>Her zaman kapalı</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><strong>SMS Ayarları</strong></div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label">Sağlayıcı</label>
                        <select name="sms_provider" class="form-select">
                            <option value="">— Yok —</option>
                            <option value="netgsm" @selected($settings->sms_provider === 'netgsm')>Netgsm</option>
                            <option value="iletimerkezi" @selected($settings->sms_provider === 'iletimerkezi')>İleti Merkezi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="text" name="sms_api_key" class="form-control" value="{{ $settings->sms_api_key }}">
                    </div>
                    <div>
                        <label class="form-label">Gönderen Adı</label>
                        <input type="text" name="sms_sender_name" class="form-control"
                               value="{{ $settings->sms_sender_name }}" placeholder="MUDAVIM">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn w-100" style="background:var(--color-sea);color:#fff;border-radius:12px;padding:12px;">
                Ayarları Kaydet
            </button>
        </div>
    </div>
</form>
@endsection
