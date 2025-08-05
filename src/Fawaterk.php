<?php 

namespace AmrEljako\FawaterkPayment;

use Illuminate\Support\Facades\Log;

class Fawaterk
{
    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function createInvoice(array $data): ?object
    {
        $response = $this->curlRequest('https://staging.fawaterk.com/api/v2/invoiceInitPay', 'POST', $data);
        return json_decode($response);
    }

    public function getInvoice(string $invoiceId): ?object
    {
        $url = "https://staging.fawaterk.com/api/v2/getInvoiceData/{$invoiceId}";
        $response = $this->curlRequest($url, 'GET');
        return json_decode($response)->data ?? null;
    }

    public function verifyWebhook(array $data): bool
    {
        if (!isset($data['hashKey'])) return false;

        $stringToHash = "InvoiceId={$data['invoice_id']}&InvoiceKey={$data['invoice_key']}&PaymentMethod={$data['payment_method']}";
        $generatedHash = hash_hmac('sha256', $stringToHash, $this->token);

        return hash_equals($generatedHash, $data['hashKey']);
    }

    private function curlRequest(string $url, string $method = 'POST', array $payload = []): string
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            Log::error('Fawaterk CURL error: ' . curl_error($curl));
        }
        curl_close($curl);

        return $response;
    }
}