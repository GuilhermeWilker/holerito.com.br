<?php

declare(strict_types=1);

namespace Holerito\controllers;

use Holerito\infraestructure\database\Database as InfraestructureDatabase;
use Holerito\Infrastructure\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InvoiceController extends Controller
{
    public function index(Request $request, Response $response): Response
    {
        $cookies = AuthController::cookiesFromSession();
        if ($cookies === null) {
            return $response->withHeader('Location', '/auth')->withStatus(302);
        }

        $pdo = InfraestructureDatabase::connection();
        $invoices = $pdo->query(
            'SELECT * FROM credit_card_invoices ORDER BY month DESC'
        )->fetchAll();

        $settings = $pdo->query(
            'SELECT * FROM financial_settings LIMIT 1'
        )->fetch();

        $html = $this->render('invoice', [
            'invoices'       => $invoices,
            'settings'       => $settings,
            'flash'          => $_SESSION['flash'] ?? null,
            'payloads'       => [],
            'currentPayload' => '',
        ]);

        unset($_SESSION['flash']);

        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $month       = trim($body['month']       ?? '');
        $amount      = trim($body['amount']       ?? '');
        $closingDay  = (int) ($body['closing_day']  ?? 15);
        $paymentBase = trim($body['payment_base'] ?? 'vale');

        if ($month === '' || $amount === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Mês e valor são obrigatórios.'];
            return $response->withHeader('Location', '/invoice-creditcard')->withStatus(302);
        }

        $amountFloat = (float) str_replace(',', '.', $amount);

        if ($amountFloat <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'O valor da fatura deve ser maior que zero.'];
            return $response->withHeader('Location', '/invoice-creditcard')->withStatus(302);
        }

        $pdo = InfraestructureDatabase::connection();
        $stmt = $pdo->prepare("
            INSERT INTO credit_card_invoices (month, amount, closing_day, payment_base, updated_at)
            VALUES (:month, :amount, :closing_day, :payment_base, CURRENT_TIMESTAMP)
            ON CONFLICT(month) DO UPDATE SET
                amount       = excluded.amount,
                closing_day  = excluded.closing_day,
                payment_base = excluded.payment_base,
                updated_at   = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ':month'        => $month,
            ':amount'       => $amountFloat,
            ':closing_day'  => $closingDay,
            ':payment_base' => $paymentBase,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Fatura salva com sucesso.'];
        return $response->withHeader('Location', '/invoice-creditcard')->withStatus(302);
    }

    public function destroy(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        $id   = (int) ($body['id'] ?? 0);

        if ($id > 0) {
            $pdo  = InfraestructureDatabase::connection();
            $stmt = $pdo->prepare('DELETE FROM credit_card_invoices WHERE id = :id');
            $stmt->execute([':id' => $id]);
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Fatura removida.'];
        return $response->withHeader('Location', '/invoice-creditcard')->withStatus(302);
    }

    public function saveSettings(Request $request, Response $response): Response
    {
        $body           = $request->getParsedBody();
        $comparisonBase = trim($body['comparison_base'] ?? 'vale');
        $idealLimitPct  = max(1, min(100, (int) ($body['ideal_limit_pct'] ?? 30)));

        $allowed = ['vale', 'salario', 'ambos'];
        if (!in_array($comparisonBase, $allowed, true)) {
            $comparisonBase = 'vale';
        }

        $pdo = InfraestructureDatabase::connection();
        $pdo->prepare("
            UPDATE financial_settings
            SET comparison_base = :base,
                ideal_limit_pct = :pct,
                updated_at      = CURRENT_TIMESTAMP
        ")->execute([':base' => $comparisonBase, ':pct' => $idealLimitPct]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Configurações salvas.'];
        return $response->withHeader('Location', '/invoice-creditcard')->withStatus(302);
    }

    public function stats(Request $request, Response $response): Response
    {
        $pdo      = InfraestructureDatabase::connection();
        $invoices = $pdo->query(
            'SELECT month, amount, payment_base FROM credit_card_invoices ORDER BY month DESC LIMIT 12'
        )->fetchAll();

        $settings = $pdo->query('SELECT * FROM financial_settings LIMIT 1')->fetch();

        $liquidSalary = (float) ($_SESSION['liquid_salary'] ?? 0);
        $valeAmount   = (float) MEAL_VOUCHER;

        $data = array_map(function (array $inv) use ($settings, $liquidSalary, $valeAmount) {
            $base = match ($inv['payment_base']) {
                'salario' => $liquidSalary,
                'ambos'   => $liquidSalary + $valeAmount,
                default   => $valeAmount,
            };

            $pct        = $base > 0 ? round(($inv['amount'] / $base) * 100, 1) : 0;
            $idealLimit = $base * ($settings['ideal_limit_pct'] / 100);
            $free       = $base - $inv['amount'];

            return [
                'month'        => $inv['month'],
                'amount'       => $inv['amount'],
                'base'         => $base,
                'pct'          => $pct,
                'ideal_limit'  => $idealLimit,
                'free'         => $free,
                'payment_base' => $inv['payment_base'],
            ];
        }, $invoices);

        $payload = json_encode(['invoices' => $data, 'settings' => $settings]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
