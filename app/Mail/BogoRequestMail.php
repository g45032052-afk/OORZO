<?php

namespace App\Mail;

use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BogoRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $name;

    protected $status;

    public function __construct($name, $status = 'pending')
    {
        $this->name = $name;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('email-templates.add-fund')->with(['data'=>$this->data]);


        $company_name = BusinessSetting::where('key', 'business_name')->first()->value;

        // denied / accepted = the restaurant's answer to an offer the admin assigned it.
        // Anything else is a restaurant asking to join off its own bat.
        $emailType = match ($this->status) {
            'denied' => 'bogo_deny',
            'accepted' => 'bogo_approve',
            default => 'bogo_request',
        };
        $data=EmailTemplate::where('type','admin')->where('email_type', $emailType)->first();

        $template=$data?$data->email_template:1;
        $restaurant_name = $this->name;
        $title = Helpers::text_variable_data_format( value:$data['title']??'',restaurant_name:$restaurant_name??'',transaction_id:$transaction_id??'');
        $body = Helpers::text_variable_data_format( value:$data['body']??'',restaurant_name:$restaurant_name??'',transaction_id:$transaction_id??'');
        $footer_text = Helpers::text_variable_data_format( value:$data['footer_text']??'',restaurant_name:$restaurant_name??'',transaction_id:$transaction_id??'');
        $copyright_text = Helpers::text_variable_data_format( value:$data['copyright_text']??'',restaurant_name:$restaurant_name??'',transaction_id:$transaction_id??'');
        return $this->subject(translate('BOGO_Request_Mail'))->view('email-templates.new-email-format-'.$template, ['company_name'=>$company_name,'data'=>$data,'title'=>$title,'body'=>$body,'footer_text'=>$footer_text,'copyright_text'=>$copyright_text]);
    }
}
