<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\States;

use Misaf\VendraInquiry\Models\Inquiry;
use Spatie\ModelStates\Transition;

final class AnswerInquiryTransition extends Transition
{
    public function __construct(private readonly Inquiry $inquiry) {}

    public function handle(): Inquiry
    {
        $this->inquiry->status = new Answered($this->inquiry);
        $this->inquiry->answered_at = now();
        $this->inquiry->save();

        return $this->inquiry;
    }
}
