<?php

namespace App\Mail;

use App\ModuleSetting;
use App\Shop;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSupplier extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $supplier;

    public $orderList;

    public $item;

    public $moduleSetting;

    public $apiUrlForEmails;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $supplier, $orderList, $item, $moduleSetting, $apiUrlForEmails)
    {
        $this->supplier = $supplier;

        $this->orderList = $orderList;

        $this->item = $item;

        $this->moduleSetting = $moduleSetting;

        $this->apiUrlForEmails = $apiUrlForEmails;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Update module settings cron status.
        $moduleSettingsUpdate = ModuleSetting::find($this->moduleSetting->id);

        $moduleSettingsUpdate->cron_status = 1;
        
        $moduleSettingsUpdate->save();
        
        return $this->markdown('emails.cron.supplier')
            ->to($this->supplier->email)
            ->subject('Order emails to suppliers')
            ->with([
                'supplier' => $this->supplier,
                'orderList' => $this->orderList,
                'item' => $this->item,
                'apiUrlForEmails' => $this->apiUrlForEmails
            ]);
    }
}
