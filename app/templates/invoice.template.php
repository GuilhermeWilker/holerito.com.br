<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holerito — Cartão × Salário</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/invoice.css">
    <link rel="shortcut icon" href="/assets/icon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
</head>

<body>
    <section class="page">

        <?php include_once('components/navbar.php') ?>

        <?php if ($flash): ?>
            <div class="flash flash--<?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <?php
        $valeAmount   = (float) MEAL_VOUCHER;
        $liquidSalary = (float) ($_SESSION['liquid_salary'] ?? 0);

        $base = match ($settings['comparison_base']) {
            'salario' => $liquidSalary,
            'ambos'   => $liquidSalary + $valeAmount,
            default   => $valeAmount,
        };

        $baseLabel = match ($settings['comparison_base']) {
            'salario' => 'Salário líquido',
            'ambos'   => 'Vale + Salário',
            default   => 'Vale alimentação',
        };

        $currentMonth   = date('Y-m');
        $currentInvoice = null;
        foreach ($invoices as $inv) {
            if ($inv['month'] === $currentMonth) {
                $currentInvoice = $inv;
                break;
            }
        }

        $currentAmount = $currentInvoice ? (float) $currentInvoice['amount'] : 0;
        $pctUsed       = $base > 0 ? round(($currentAmount / $base) * 100, 1) : 0;
        $idealLimit    = $base * ($settings['ideal_limit_pct'] / 100);
        $freeAmount    = $base - $currentAmount;
        $overIdeal     = $currentAmount > $idealLimit;

        // Donut math — circumference of r=30 circle = 188.5
        $circumference = 188.5;
        $pctDash       = min($pctUsed, 100) / 100 * $circumference;
        $freePct       = $base > 0 ? min(max(($freeAmount / $base) * 100, 0), 100) : 0;
        $freeDash      = $freePct / 100 * $circumference;

        $pctColor  = $pctUsed >= $settings['ideal_limit_pct'] ? '#c0392b' : '#3b82c4';
        $freeColor = $freeAmount < 0 ? '#c0392b' : '#2e9e6b';
        ?>

        <!-- ── Cards row (mesmo padrão do index) ─────────────────────────────── -->
        <div class="cards">

            <!-- Badges flutuantes -->
            <div class="badges-container">
                <div class="badge">
                    <?= __moneyFormatter($base) ?>
                    <small><?= $baseLabel ?></small>
                </div>
                <div class="badge badge--pct <?= $overIdeal ? 'badge--warn' : 'badge--ok' ?>">
                    <?= number_format($pctUsed, 1) ?>% comprometido
                </div>
                <div class="badge">
                    <?= __moneyFormatter($currentAmount) ?>
                    <small>fatura <?= date('m/Y') ?></small>
                </div>
                <div class="badge">
                    <?= __moneyFormatter($idealLimit) ?>
                    <small>limite ideal (<?= $settings['ideal_limit_pct'] ?>%)</small>
                </div>
            </div>

            <!-- Card principal: saldo livre -->
            <div class="card card-salary">
                <?php if ($currentAmount > 0): ?>
                    <span class="salary-diff <?= $overIdeal ? 'negative' : '' ?>">
                        <?= $overIdeal ? '▲ acima do limite' : '▼ dentro do limite' ?>
                    </span>
                <?php endif; ?>
                <p class="salary-amount <?= $freeAmount < 0 ? 'salary-amount--danger' : '' ?>">
                    <?= __moneyFormatter($freeAmount) ?>
                </p>
                <p class="salary-label">Saldo livre este mês</p>
            </div>

            <!-- Donut: % comprometido -->
            <div class="card card-donut">
                <div class="donut-wrap">
                    <svg width="80" height="80" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="30" fill="none" stroke="<?= $pctUsed >= $settings['ideal_limit_pct'] ? '#fde8e8' : '#dbeafe' ?>" stroke-width="10" />
                        <circle cx="40" cy="40" r="30" fill="none" stroke="<?= $pctColor ?>" stroke-width="10"
                            stroke-dasharray="<?= round($pctDash, 3) ?> <?= $circumference ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 40 40)" />
                    </svg>
                    <div class="donut-center">
                        <span class="val" style="color:<?= $pctColor ?>">
                            <?= number_format($pctUsed, 1) ?>%
                        </span>
                    </div>
                </div>
                <span class="donut-label">Comprometido</span>
            </div>

            <!-- Donut: saldo livre % -->
            <div class="card card-donut">
                <div class="donut-wrap">
                    <svg width="80" height="80" viewBox="0 0 80 80">
                        <circle cx="40" cy="40" r="30" fill="none" stroke="<?= $freeAmount < 0 ? '#fde8e8' : '#d1fae5' ?>" stroke-width="10" />
                        <circle cx="40" cy="40" r="30" fill="none" stroke="<?= $freeColor ?>" stroke-width="10"
                            stroke-dasharray="<?= round($freeDash, 3) ?> <?= $circumference ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 40 40)" />
                    </svg>
                    <div class="donut-center">
                        <span class="val" style="color:<?= $freeColor ?>">
                            <?= number_format($freePct, 1) ?>%
                        </span>
                    </div>
                </div>
                <span class="donut-label">Livre</span>
            </div>

        </div><!-- /.cards -->

        <!-- ── Gráfico de barras ─────────────────────────────────────────────── -->
        <div class="mirror-card inv-chart-card">
            <div class="inv-chart-header">
                <span class="inv-section-title">Fatura vs. <?= $baseLabel ?></span>
                <div class="inv-legend">
                    <span class="inv-leg-dot" style="background:#ED93B1"></span> Fatura
                    <span class="inv-leg-dot" style="background:#97C459; margin-left:10px"></span> <?= $baseLabel ?>
                    <span class="inv-leg-dot" style="background:#2a27ef; margin-left:10px"></span> Limite ideal
                </div>
            </div>
            <div class="inv-chart" id="inv-chart">
                <?php if (empty($invoices)): ?>
                    <p class="inv-empty">Nenhuma fatura registrada ainda. Adicione a primeira abaixo.</p>
                <?php else: ?>
                    <?php
                    $chartInvoices = array_slice(array_reverse($invoices), 0, 6);
                    $maxVal = max(array_merge(
                        array_column($chartInvoices, 'amount'),
                        [$base]
                    )) * 1.15;
                    ?>
                    <div class="inv-bars">
                        <?php foreach ($chartInvoices as $inv): ?>
                            <?php
                            $invAmt     = (float) $inv['amount'];
                            $barH       = round(($invAmt     / $maxVal) * 140);
                            $baseH      = round(($base       / $maxVal) * 140);
                            $limitH     = round(($idealLimit / $maxVal) * 140);
                            $month      = \DateTimeImmutable::createFromFormat('Y-m', $inv['month']);
                            $monthLabel = $month ? mb_strtolower($month->format('M/y')) : $inv['month'];
                            $isOver     = $invAmt > $idealLimit;
                            ?>
                            <div class="inv-bar-group">
                                <div class="inv-bar-col">
                                    <div class="inv-bar inv-bar--invoice <?= $isOver ? 'inv-bar--over' : '' ?>"
                                        style="height:<?= $barH ?>px"
                                        title="<?= __moneyFormatter($invAmt) ?>"></div>
                                    <div class="inv-bar inv-bar--base"
                                        style="height:<?= $baseH ?>px"
                                        title="<?= __moneyFormatter($base) ?>"></div>
                                </div>
                                <div class="inv-limit-line" style="bottom:calc(24px + <?= $limitH ?>px)"></div>
                                <span class="inv-bar-month"><?= $monthLabel ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="inv-limit-legend">— limite ideal</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Insights automáticos ──────────────────────────────────────────── -->
        <?php if (!empty($invoices)): ?>
            <div class="mirror-card inv-insights-card">
                <span class="inv-section-title" style="display:block;margin-bottom:12px">Insights automáticos</span>

                <?php
                $sorted = $invoices;
                $last   = isset($sorted[0]) ? (float) $sorted[0]['amount'] : null;
                $prev   = isset($sorted[1]) ? (float) $sorted[1]['amount'] : null;
                ?>

                <?php if ($last !== null): ?>
                    <div class="inv-insight <?= $overIdeal ? 'inv-insight--warn' : 'inv-insight--ok' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?php if ($overIdeal): ?>
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            <?php else: ?>
                                <polyline points="20 6 9 17 4 12" />
                            <?php endif; ?>
                        </svg>
                        <div>
                            <strong><?= $overIdeal ? 'Fatura acima do limite este mês' : 'Fatura dentro do limite este mês' ?></strong><br>
                            <small>
                                <?php if ($overIdeal): ?>
                                    A fatura de <?= __moneyFormatter($currentAmount) ?> supera o ideal de <?= __moneyFormatter($idealLimit) ?>. Considere reduzir gastos ou ajustar o limite.
                                <?php else: ?>
                                    Você está usando <?= number_format($pctUsed, 1) ?>% da base — abaixo do limite de <?= $settings['ideal_limit_pct'] ?>%.
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($last !== null && $prev !== null): ?>
                    <?php $diff = $last - $prev;
                    $diffPct = $prev > 0 ? round(($diff / $prev) * 100, 1) : 0; ?>
                    <div class="inv-insight <?= $diff > 0 ? 'inv-insight--warn' : 'inv-insight--ok' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <?php if ($diff > 0): ?>
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                <polyline points="17 6 23 6 23 12" />
                            <?php else: ?>
                                <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
                                <polyline points="17 18 23 18 23 12" />
                            <?php endif; ?>
                        </svg>
                        <div>
                            <strong>Fatura <?= $diff > 0 ? 'subiu' : 'caiu' ?> <?= abs($diffPct) ?>% em relação ao mês anterior</strong><br>
                            <small>
                                <?= $diff > 0 ? 'Aumento' : 'Redução' ?> de <?= __moneyFormatter(abs($diff)) ?>
                                — de <?= __moneyFormatter($prev) ?> para <?= __moneyFormatter($last) ?>.
                            </small>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $avgAmount    = count($invoices) > 0 ? array_sum(array_column($invoices, 'amount')) / count($invoices) : 0;
                $suggestedPct = $base > 0 ? (int) ceil(($avgAmount / $base) * 100) : $settings['ideal_limit_pct'];
                ?>
                <?php if ($avgAmount > 0 && $suggestedPct !== (int) $settings['ideal_limit_pct']): ?>
                    <div class="inv-insight inv-insight--info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div>
                            <strong>Limite sugerido com base no histórico: <?= $suggestedPct ?>%</strong><br>
                            <small>
                                Sua média de fatura é <?= __moneyFormatter($avgAmount) ?>,
                                equivalente a <?= $suggestedPct ?>% da base.
                                Seu limite atual está em <?= $settings['ideal_limit_pct'] ?>%.
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ── Grid: Formulário + Configurações ─────────────────────────────── -->
        <div class="inv-forms-grid">

            <div class="mirror-card inv-form-card">
                <span class="inv-section-title">Lançar fatura</span>
                <form action="/invoice-creditcard" method="post" class="inv-form">
                    <div class="inv-field">
                        <label class="inv-label" for="month">Mês de referência</label>
                        <input class="inv-input" type="month" id="month" name="month" value="<?= date('Y-m') ?>" required />
                    </div>
                    <div class="inv-field">
                        <label class="inv-label" for="amount">Valor da fatura (R$)</label>
                        <input class="inv-input" type="number" id="amount" name="amount" placeholder="0,00" min="0.01" step="0.01" required />
                    </div>
                    <div class="inv-field">
                        <label class="inv-label" for="closing_day">Dia de fechamento</label>
                        <input class="inv-input" type="number" id="closing_day" name="closing_day" min="1" max="31" value="15" />
                    </div>
                    <div class="inv-field">
                        <label class="inv-label" for="payment_base">Pagar com</label>
                        <select class="inv-input inv-select" id="payment_base" name="payment_base">
                            <option value="vale" <?= $settings['comparison_base'] === 'vale'    ? 'selected' : '' ?>>Vale alimentação</option>
                            <option value="salario" <?= $settings['comparison_base'] === 'salario' ? 'selected' : '' ?>>Salário líquido</option>
                            <option value="ambos" <?= $settings['comparison_base'] === 'ambos'   ? 'selected' : '' ?>>Vale + Salário</option>
                        </select>
                    </div>
                    <button type="submit" class="inv-btn inv-btn--primary">Salvar fatura</button>
                </form>
            </div>

            <div class="mirror-card inv-form-card">
                <span class="inv-section-title">Configurações</span>
                <form action="/invoice-creditcard/settings" method="post" class="inv-form">
                    <div class="inv-field">
                        <label class="inv-label" for="comparison_base">Base de comparação padrão</label>
                        <select class="inv-input inv-select" id="comparison_base" name="comparison_base">
                            <option value="vale" <?= $settings['comparison_base'] === 'vale'    ? 'selected' : '' ?>>Vale alimentação</option>
                            <option value="salario" <?= $settings['comparison_base'] === 'salario' ? 'selected' : '' ?>>Salário líquido</option>
                            <option value="ambos" <?= $settings['comparison_base'] === 'ambos'   ? 'selected' : '' ?>>Vale + Salário</option>
                        </select>
                        <small class="inv-hint">
                            Vale = <?= __moneyFormatter($valeAmount) ?> &nbsp;·&nbsp;
                            Salário liq. = <?= $liquidSalary > 0 ? __moneyFormatter($liquidSalary) : 'carregue o espelho primeiro' ?>
                        </small>
                    </div>
                    <div class="inv-field">
                        <label class="inv-label" for="ideal_limit_pct">
                            Limite ideal (% da base) — atual: <?= $settings['ideal_limit_pct'] ?>%
                        </label>
                        <input
                            class="inv-input"
                            type="range"
                            id="ideal_limit_pct"
                            name="ideal_limit_pct"
                            min="5" max="100" step="5"
                            value="<?= $settings['ideal_limit_pct'] ?>"
                            oninput="document.getElementById('pct-display').textContent = this.value + '%'" />
                        <span class="inv-pct-display" id="pct-display"><?= $settings['ideal_limit_pct'] ?>%</span>
                    </div>
                    <button type="submit" class="inv-btn inv-btn--secondary">Salvar configurações</button>
                </form>
            </div>

        </div>

        <!-- ── Histórico de faturas ──────────────────────────────────────────── -->
        <div class="mirror-card" style="margin-top:1.25rem;">
            <div class="mirror-header" style="grid-template-columns:100px 1fr 120px 120px 60px;">
                <span>Mês</span>
                <span>Base de pagamento</span>
                <span>Fatura</span>
                <span>% da base</span>
                <span></span>
            </div>
            <div class="mirror-body">
                <?php if (empty($invoices)): ?>
                    <div class="mirror-row" style="justify-content:center;color:var(--muted);font-size:0.8rem;">
                        Nenhuma fatura registrada.
                    </div>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <?php
                        $invBase = match ($inv['payment_base']) {
                            'salario' => $liquidSalary,
                            'ambos'   => $liquidSalary + $valeAmount,
                            default   => $valeAmount,
                        };
                        $invPct  = $invBase > 0 ? round(($inv['amount'] / $invBase) * 100, 1) : 0;
                        $invOver = $inv['amount'] > ($invBase * ($settings['ideal_limit_pct'] / 100));
                        $monthDt  = \DateTimeImmutable::createFromFormat('Y-m', $inv['month']);
                        $monthFmt = $monthDt ? $monthDt->format('M/Y') : $inv['month'];
                        $baseLabels = ['vale' => 'Vale', 'salario' => 'Salário', 'ambos' => 'Vale + Salário'];
                        ?>
                        <div class="mirror-row" style="grid-template-columns:100px 1fr 120px 120px 60px;">
                            <span class="day-col"><?= htmlspecialchars($monthFmt) ?></span>
                            <span style="font-size:0.78rem;color:var(--muted)">
                                <?= $baseLabels[$inv['payment_base']] ?? $inv['payment_base'] ?>
                            </span>
                            <span>
                                <span class="time-pill" style="font-family:'DM Mono',monospace;">
                                    <?= __moneyFormatter((float) $inv['amount']) ?>
                                </span>
                            </span>
                            <span>
                                <span class="saldo <?= $invOver ? 'negative' : 'positive' ?>">
                                    <?= number_format($invPct, 1) ?>%
                                </span>
                            </span>
                            <span>
                                <form action="/invoice-creditcard/delete" method="post"
                                    onsubmit="return confirm('Remover fatura de <?= htmlspecialchars($monthFmt) ?>?')">
                                    <input type="hidden" name="id" value="<?= (int) $inv['id'] ?>">
                                    <button type="submit" class="inv-btn-del" title="Remover">✕</button>
                                </form>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </section>
</body>

</html>