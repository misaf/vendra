<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Actions;

use Misaf\VendraInquiry\Models\Inquiry;

final class ReopenInquiryAction
{
    public function execute(Inquiry $inquiry): void
    {
        $inquiry->reopen();
    }
}
