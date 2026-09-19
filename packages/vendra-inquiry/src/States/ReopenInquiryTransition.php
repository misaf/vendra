<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Misaf\VendraInquiry\Models\Inquiry;
use Spatie\ModelStates\Transition;

final class ReopenInquiryTransition extends Transition
{
    public function __construct(private readonly Inquiry $inquiry) {}

    public function handle(): Inquiry
    {
        $this->inquiry->status = new Open($this->inquiry);
        $this->inquiry->answered_at = null;
        $this->inquiry->save();

        return $this->inquiry;
    }
}
