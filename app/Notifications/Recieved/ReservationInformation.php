<?php

namespace App\Notifications\Recieved;

use App\Http\Resources\Reservation\ReservationIndexResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationInformation extends Notification
{
    use Queueable;

    public $reservation;
    public $order;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->rservation = $data['reservation'];
        $this->order = $data['order'];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'reservation' => $this->reservation,
            'order' => $this->order
        ];
    }
}
