<?php

namespace App\Mail;

use App\Cron;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToSupplier extends Mailable /* implements ShouldQueue */
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
        // Storing cron details.
        $cron = new Cron;

        $cron->module_setting_id = $this->moduleSetting->id;

        $cron->api_order_no = $this->orderList['order_no'];

        $cron->cron_status = 1;

        $cron->save();

        return $this->markdown('emails.cron.supplier')
            ->to($this->supplier->email)
            ->subject($this->moduleSetting->mail_subject)
            ->with([
                'supplier' => $this->supplier,
                'orderList' => $this->orderList,
                'item' => $this->item,
                'apiUrlForEmails' => $this->apiUrlForEmails,
                'moduleSetting' => $this->moduleSetting,
            ]);
    }
}
