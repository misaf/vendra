<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Support\Facades\App;
use LogicException;
use Misaf\VendraSubscription\Models\SubscriptionInvoice;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

final class SubscriptionInvoicePdf
{
    private const array RTL_LOCALES = ['ar', 'fa', 'he', 'ur'];

    /**
     * Render from the invoice's own snapshot, so a download always matches what was issued.
     */
    public static function render(SubscriptionInvoice $invoice, ?string $locale = null): string
    {
        $previousLocale = App::getLocale();
        $locale ??= $previousLocale;
        App::setLocale($locale);

        try {
            $rtl = in_array($locale, self::RTL_LOCALES, true);

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => storage_path('framework/cache/mpdf'),
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);
            $mpdf->SetDirectionality($rtl ? 'rtl' : 'ltr');
            $mpdf->SetTitle($invoice->number);
            $mpdf->WriteHTML(view('vendra-subscription::invoices.pdf', ['invoice' => $invoice, 'rtl' => $rtl])->render());

            $pdf = $mpdf->Output($invoice->downloadName(), Destination::STRING_RETURN);

            throw_unless(is_string($pdf), LogicException::class, "Invoice [{$invoice->number}] could not be rendered.");

            return $pdf;
        } finally {
            App::setLocale($previousLocale);
        }
    }
}
