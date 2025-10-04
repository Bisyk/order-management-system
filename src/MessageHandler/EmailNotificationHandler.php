<?php

namespace App\MessageHandler;

use App\Message\OrderEmailNotificationMessage;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class EmailNotificationHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        private readonly string $senderEmail
    ) {
    }

    public function __invoke(OrderEmailNotificationMessage $message): void
    {
        try {
            $email = $this->createEmail($message);
            $this->mailer->send($email);
            
            $this->logger->info('Email notification sent successfully', [
                'order_id' => $message->getOrderId(),
                'customer_email' => $message->getCustomerEmail(),
                'notification_type' => $message->getNotificationType()
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to send email notification', [
                'order_id' => $message->getOrderId(),
                'customer_email' => $message->getCustomerEmail(),
                'notification_type' => $message->getNotificationType(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function createEmail(OrderEmailNotificationMessage $message): Email
    {
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($message->getCustomerEmail())
            ->subject($this->getEmailSubject($message->getNotificationType()))
            ->html($this->getEmailBody($message));

        return $email;
    }

    private function getEmailSubject(string $notificationType): string
    {
        return match ($notificationType) {
            'order_created' => 'Order Confirmation - Your order has been placed',
            'order_shipped' => 'Order Shipped - Your order is on its way',
            'order_delivered' => 'Order Delivered - Your order has been delivered',
            default => 'Order Update'
        };
    }

    private function getEmailBody(OrderEmailNotificationMessage $message): string
    {
        $customerName = $message->getCustomerName();
        $orderId = $message->getOrderId();
        $orderData = $message->getOrderData();

        return match ($message->getNotificationType()) {
            'order_created' => $this->createOrderCreatedTemplate($customerName, $orderId, $orderData),
            'order_shipped' => $this->createOrderShippedTemplate($customerName, $orderId, $orderData),
            'order_delivered' => $this->createOrderDeliveredTemplate($customerName, $orderId, $orderData),
            default => $this->createDefaultTemplate($customerName, $orderId, $orderData)
        };
    }

    private function createOrderCreatedTemplate(string $customerName, int $orderId, array $orderData): string
    {
        $totalAmount = $orderData['total_amount'] ?? 'N/A';
        $itemsHtml = $this->formatOrderItems($orderData['items'] ?? []);

        return "
        <html>
        <body>
            <h2>Order Confirmation</h2>
            <p>Dear {$customerName},</p>
            <p>Thank you for your order! Your order has been successfully placed.</p>
            <p><strong>Order ID:</strong> #{$orderId}</p>
            <p><strong>Total Amount:</strong> \${$totalAmount}</p>
            <h3>Order Items:</h3>
            {$itemsHtml}
            <p>We will notify you when your order status changes.</p>
            <p>Thank you for choosing our service!</p>
        </body>
        </html>
        ";
    }

    private function createOrderShippedTemplate(string $customerName, int $orderId, array $orderData): string
    {
        return "
        <html>
        <body>
            <h2>Order Shipped</h2>
            <p>Dear {$customerName},</p>
            <p>Great news! Your order has been shipped and is on its way to you.</p>
            <p><strong>Order ID:</strong> #{$orderId}</p>
            <p>Thank you for your patience!</p>
        </body>
        </html>
        ";
    }

    private function createOrderDeliveredTemplate(string $customerName, int $orderId, array $orderData): string
    {
        return "
        <html>
        <body>
            <h2>Order Delivered</h2>
            <p>Dear {$customerName},</p>
            <p>Your order has been successfully delivered!</p>
            <p><strong>Order ID:</strong> #{$orderId}</p>
            <p>We hope you enjoy your purchase. If you have any questions or concerns, please don't hesitate to contact us.</p>
            <p>Thank you for choosing our service!</p>
        </body>
        </html>
        ";
    }

    private function createDefaultTemplate(string $customerName, int $orderId, array $orderData): string
    {
        return "
        <html>
        <body>
            <h2>Order Update</h2>
            <p>Dear {$customerName},</p>
            <p>We wanted to update you about your order.</p>
            <p><strong>Order ID:</strong> #{$orderId}</p>
            <p>Thank you for choosing our service!</p>
        </body>
        </html>
        ";
    }

    private function formatOrderItems(array $items): string
    {
        if (empty($items)) {
            return '<p>No items found.</p>';
        }

        $html = '<ul>';
        foreach ($items as $item) {
            $name = $item['product_name'] ?? 'Unknown Product';
            $quantity = $item['quantity'] ?? 1;
            $price = $item['price'] ?? '0.00';
            $html .= "<li>{$name} - Quantity: {$quantity} - Price: \${$price}</li>";
        }
        $html .= '</ul>';

        return $html;
    }
}