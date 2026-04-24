<?php

namespace App\Jobs;

use App\Events\QrCodeGenerated;
use App\Models\Url;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQrCode implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Url $urlShortened)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $slug = $this->urlShortened->id;
        $url = config("app.url") . "/r/" . $slug . "?from=qrcode";
        $qrcode = QrCode::generate($url);
        $this->urlShortened->qr_code = $qrcode;
        $this->urlShortened->save();
        event(new QrCodeGenerated($this->urlShortened));
    }
}
