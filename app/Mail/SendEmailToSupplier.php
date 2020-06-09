<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSupplier extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $supplier;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Writing the entry on log for testing.
        Log::info('Mailable - Supplier Details: ' . $this->supplier);

        return $this->markdown('emails.cron.supplier')
            ->to($this->supplier->email)
            ->subject('Test emails to supplier')
            ->with('supplier', $this->supplier);
    }
}
