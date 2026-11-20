<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerEnquiryReceived extends Notification
{
    use Queueable;

    public function __construct(private readonly Enquiry $enquiry) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New enquiry about '.$this->enquiry->listing->title)
            ->replyTo($this->enquiry->sender_email, $this->enquiry->sender_name)
            ->greeting('You have a new MarketNest enquiry')
            ->line($this->enquiry->sender_name.' sent a message about "'.$this->enquiry->listing->title.'".')
            ->line($this->enquiry->message)
            ->action('View listing', route('listings.show', $this->enquiry->listing));
    }
}
