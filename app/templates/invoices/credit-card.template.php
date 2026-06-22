<!DOCTYPE html>
<html lang="pt-BR">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Holerito — Fatura × Salário</title>
      <link rel="stylesheet" href="/assets/css/app.css">
      <link rel="stylesheet" href="/assets/css/invoice.css">
      <link rel="shortcut icon" href="/assets/icon.png" type="image/x-icon">
      <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
</head>

<body>
      <section class="page">

            <?php include_once('components/navbar.php') ?>

            <!-- ── Métricas rápidas ──────────────────────────────────────────────── -->
            <div class="inv-metrics">

                  <div class="inv-metric <?= $usedPct > ($settings['ideal_limit_pct'] + 10) ? 'inv-metric--danger' : ($usedPct > $settings['ideal_limit_pct'] ? 'inv-metric--warn' : 'inv-metric--ok') ?>">
                        <span class="inv-metric__val"><?= $usedPct ?>%</span>
                        <span class="inv-metric__lbl">Base comprometida</span>
                  </div>

                  <div class="inv-metric">
                        <span class="inv-metric__val"><?= __moneyFormatter($freeSaldo) ?></span>
                        <span class="inv-metric__lbl">Saldo livre</span>
                  </div>

                  <div class="inv-metric <?= $overIdeal > 0 ? 'inv-metric--danger' : 'inv-metric--ok' ?>">
                        <span class="inv-metric__val">
                              <?= $overIdeal > 0
                                    ? '+' . __moneyFormatter($overIdeal)
                                    : __moneyFormatter(abs($overIdeal)) . ' abaixo' ?>
                        </span>
                        <span class="inv-metric__lbl">Vs. limite ideal (<?= $settings['ideal_limit_pct'] ?>%)</span>
                  </div>

                  <div class="inv-metric">
                        <span class="inv-metric__val"><?= __moneyFormatter($idealLimit) ?></span>
                        <span class="inv-metric__lbl">Limite ideal</span>
                  </div>

            </div>

            <!-- ── Corpo: gráfico + painel direito ───────────────────────────────── -->
            <div class="inv-body">

                  <!-- Coluna esquerda: gráfico + insights -->
                  <div class="inv-left">

                        <!-- Gráfico de barras -->
                        <div class="mirror-card inv-chart-card">
                              <div class="inv-section-title">Histórico fatura × base</div>

                              <?php if (empty($chartData)): ?>
                                    <p class="inv-empty">Nenhuma fatura lançada ainda.</p>
                              <?php else: ?>
                                    <div class="inv-chart">
                                          <?php
                                          $maxVal = max(array_map(fn($d) => max($d['invoice'], $d['base']), $chartData));
                                          ?>
                                          <?php foreach ($chartData as $point): ?>
                                                <?php
                                                $invH  = $maxVal > 0 ? round(($point['invoice'] / $maxVal) * 130) : 0;
                                                $baseH = $maxVal > 0 ? round(($point['base']    / $maxVal) * 130) : 0;
                                                $over  = $point['invoice'] > $point['base'];
                                                ?>
                                                <div class="inv-chart__group">
                                                      <div class="inv-chart__bars">
                                                            <div
                                                                  class="inv-chart__bar inv-chart__bar--invoice <?= $over ? 'inv-chart__bar--over' : '' ?>"
                                                                  style="height: <?= $invH ?>px"
                                                                  title="Fatura: <?= __moneyFormatter($point['invoice']) ?>"></div>
                                                            <div
                                                                  class="inv-chart__bar inv-chart__bar--base"
                                                                  style="height: <?= $baseH ?>px"
                                                                  title="Base: <?= __moneyFormatter($point['base']) ?>"></div>
                                                      </div>
                                                      <span class="inv-chart__label"><?= htmlspecialchars($point['month']) ?></span>
                                                </div>
                                          <?php endforeach; ?>
                                    </div>

                                    <div class="inv-legend">
                                          <span class="inv-legend__dot inv-legend__dot--invoice"></span> Fatura
                                          <span class="inv-legend__dot inv-legend__dot--base" style="margin-left:1rem"></span>
                                          <?= match ($settings['comparison_base']) {
                                                'salario' => 'Salário líquido',
                                                'ambos'   => 'Salário + Vale',
                                                default   => 'Vale alimentação',
                                          } ?>
                                    </div>
                              <?php endif; ?>
                        </div>

                        <!-- Insights -->
                        <?php if (!empty($insights)): ?>
                              <div class="mirror-card inv-insights">
                                    <div class="inv-section-title">Insights automáticos</div>
                                    <?php foreach ($insights as $insight): ?>
                                          <div class="inv-insight inv-insight--<?= htmlspecialchars($insight['type']) ?>">
                                                <div class="inv-insight__icon">
                                                      <?php if ($insight['type'] === 'ok'): ?>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                                  <polyline points="20 6 9 17 4 12" />
                                                            </svg>
                                                      <?php elseif ($insight['type'] === 'danger'): ?>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                                  <line x1="12" y1="8" x2="12" y2="12" />
                                                                  <line x1="12" y1="16" x2="12.01" y2="16" />
                                                                  <circle cx="12" cy="12" r="10" />
                                                            </svg>
                                                      <?php elseif ($insight['type'] === 'warn'): ?>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                                  <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                                                            </svg>
                                                      <?php else: ?>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                                  <circle cx="12" cy="12" r="10" />
                                                                  <line x1="12" y1="16" x2="12" y2="12" />
                                                                  <line x1="12" y1="8" x2="12.01" y2="8" />
                                                            </svg>
                                                      <?php endif; ?>
                                                </div>
                                                <div class="inv-insight__text">
                                                      <strong><?= htmlspecialchars($insight['title']) ?></strong>
                                                      <span><?= htmlspecialchars($insight['body']) ?></span>
                                                </div>
                                          </div>
                                    <?php endforeach; ?>
                              </div>
                        <?php endif; ?>

                  </div>

                  <!-- Coluna direita: formulários + histórico -->
                  <div class="inv-right">

                        <!-- Configurações de base -->
                        <div class="mirror-card">
                              <div class="inv-section-title">Configurações</div>
                              <form action="/invoice-creditcard/settings" method="post" class="inv-form">

                                    <div class="inv-form__group">
                                          <label class="inv-form__label">Base de comparação</label>
                                          <div class="inv-base-btns">
                                                <?php foreach (['vale' => 'Vale alimentação', 'salario' => 'Salário líquido', 'ambos' => 'Ambos'] as $val => $label): ?>
                                                      <label class="inv-base-btn <?= $settings['comparison_base'] === $val ? 'inv-base-btn--active' : '' ?>">
                                                            <input type="radio" name="comparison_base" value="<?= $val ?>"
                                                                  <?= $settings['comparison_base'] === $val ? 'checked' : '' ?>>
                                                            <?= $label ?>
                                                      </label>
                                                <?php endforeach; ?>
                                          </div>
                                    </div>

                                    <div class="inv-form__group">
                                          <label class="inv-form__label" for="ideal_limit_pct">
                                                Limite ideal (% da base)
                                          </label>
                                          <div class="inv-form__row">
                                                <input
                                                      class="input inv-form__input"
                                                      type="number"
                                                      id="ideal_limit_pct"
                                                      name="ideal_limit_pct"
                                                      min="5" max="100" step="5"
                                                      value="<?= (int) $settings['ideal_limit_pct'] ?>">
                                                <span class="inv-form__suffix">%</span>
                                          </div>
                                    </div>

                                    <button type="submit" class="inv-btn inv-btn--primary">Salvar configurações</button>
                              </form>
                        </div>

                        <!-- Lançar fatura -->
                        <div class="mirror-card">
                              <div class="inv-section-title">
                                    <?= $currentInvoice ? 'Atualizar fatura' : 'Lançar fatura' ?>
                              </div>

                              <?php if (isset($_SESSION['invoice_error'])): ?>
                                    <div class="inv-error">
                                          <?= htmlspecialchars($_SESSION['invoice_error']) ?>
                                          <?php unset($_SESSION['invoice_error']); ?>
                                    </div>
                              <?php endif; ?>

                              <form action="/invoice-creditcard" method="post" class="inv-form">

                                    <div class="inv-form__group">
                                          <label class="inv-form__label" for="month">Mês de referência</label>
                                          <input
                                                class="input inv-form__input"
                                                type="month"
                                                id="month"
                                                name="month"
                                                value="<?= htmlspecialchars($currentInvoice['month'] ?? $currentMonth) ?>">
                                    </div>

                                    <div class="inv-form__group">
                                          <label class="inv-form__label" for="amount">Valor da fatura (R$)</label>
                                          <input
                                                class="input inv-form__input"
                                                type="text"
                                                id="amount"
                                                name="amount"
                                                placeholder="Ex: 850,00"
                                                value="<?= $currentInvoice ? number_format($currentInvoice['amount'], 2, ',', '.') : '' ?>">
                                    </div>

                                    <div class="inv-form__group">
                                          <label class="inv-form__label" for="payment_base">Pago com</label>
                                          <select class="input inv-form__input" id="payment_base" name="payment_base">
                                                <?php foreach (['vale' => 'Vale alimentação', 'salario' => 'Salário', 'ambos' => 'Vale + Salário'] as $val => $lbl): ?>
                                                      <option value="<?= $val ?>" <?= ($currentInvoice['payment_base'] ?? 'vale') === $val ? 'selected' : '' ?>>
                                                            <?= $lbl ?>
                                                      </option>
                                                <?php endforeach; ?>
                                          </select>
                                    </div>

                                    <div class="inv-form__group">
                                          <label class="inv-form__label" for="closing_day">Dia de fechamento</label>
                                          <input
                                                class="input inv-form__input"
                                                type="number"
                                                id="closing_day"
                                                name="closing_day"
                                                min="1" max="31"
                                                value="<?= (int) ($currentInvoice['closing_day'] ?? 15) ?>">
                                    </div>

                                    <button type="submit" class="inv-btn inv-btn--primary">
                                          <?= $currentInvoice ? 'Atualizar' : 'Salvar fatura' ?>
                                    </button>
                              </form>
                        </div>

                        <!-- Histórico -->
                        <?php if (!empty($invoices)): ?>
                              <div class="mirror-card">
                                    <div class="inv-section-title">Histórico</div>
                                    <div class="inv-history">
                                          <?php foreach ($invoices as $inv): ?>
                                                <div class="inv-history__row">
                                                      <div class="inv-history__info">
                                                            <span class="inv-history__month">
                                                                  <?= htmlspecialchars($inv['month']) ?>
                                                            </span>
                                                            <span class="inv-history__base time-pill">
                                                                  <?= match ($inv['payment_base']) {
                                                                        'salario' => 'Salário',
                                                                        'ambos'   => 'Vale + Salário',
                                                                        default   => 'Vale',
                                                                  } ?>
                                                            </span>
                                                      </div>
                                                      <div class="inv-history__right">
                                                            <span class="inv-history__amount">
                                                                  <?= __moneyFormatter($inv['amount']) ?>
                                                            </span>
                                                            <form action="/invoice-creditcard/<?= $inv['id'] ?>/delete" method="post">
                                                                  <button type="submit" class="inv-delete" title="Remover">
                                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                              <polyline points="3 6 5 6 21 6" />
                                                                              <path d="M19 6l-1 14H6L5 6" />
                                                                              <path d="M10 11v6M14 11v6" />
                                                                              <path d="M9 6V4h6v2" />
                                                                        </svg>
                                                                  </button>
                                                            </form>
                                                      </div>
                                                </div>
                                          <?php endforeach; ?>
                                    </div>
                              </div>
                        <?php endif; ?>

                  </div>
            </div>

      </section>
</body>

</html>