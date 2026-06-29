<?php

declare(strict_types=1);

namespace App\Svc;

class PaymentGateway
{
    /** Processa pagamento e retorna ID da transação. */
    public function charge(Money $amount): string
    {
    }

    public function refund(string $id): void
    {
    }

    /**
     * Lista cobranças pendentes.
     *
     * @return array<int, Charge>
     * @throws GatewayException quando o provedor está indisponível
     */
    protected function pending(): array
    {
        return [];
    }
}
