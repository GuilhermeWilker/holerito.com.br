<!DOCTYPE html>
<html lang="en">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Holerito</title>
      <link rel="stylesheet" href="assets/css/app.css">
      <link rel="shortcut icon" href="/assets/icon.png" type="image/x-icon">
      <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
      <script src="/assets/js/app.js" module defer></script>
</head>

<body>
      <section class="page">

            <!-- ── Top bar ─────────────────────────────────────────────────── -->
            <header class="topbar">
                  <div class="nav-links">
                        <!-- <a href="/" <?= $_SERVER['REQUEST_URI'] === '/' ? 'class="active"' : '' ?>> -->
                        <a href="/">
                              Espelho de Ponto
                        </a>

                        <form action="/add/payload" method="post">
                              <select name="payload" class="input" onchange="this.form.submit()">
                                    <?php foreach ($payloads as $label => $value): ?>
                                          <option value="<?= $value ?>" <?= $value === $currentPayload ? 'selected' : '' ?>>
                                                <?= $label ?>
                                          </option>
                                    <?php endforeach; ?>
                              </select>
                        </form>

                        <form action="/logout" method="post">
                              <button type="submit" class="input">Sair</button>
                        </form>
                  </div>
                  <div class="employee-badge">
                        <div class="employee-avatar">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                              </svg>
                        </div>
                        <span class="employee-id">1331</span>
                  </div>
            </header>

            <!-- ── Cards ───────────────────────────────────────────────────── -->
            <div class="cards">

                  <!-- Bullet -->
                  <div class="badges-container">

                        <div class="badge">
                              <?= __moneyFormatter($data['employee']['salary']) ?>
                              <small>salário bruto</small>
                        </div>

                        <div class="badge">
                              <?= __moneyFormatter($data['employee']['benefit']) ?>
                              <small>vale</small>
                        </div>
                        <div class="badge">
                              <?= __moneyFormatter(($data['employee']['liquid_salary'] + $data['employee']['benefit']) - $data['employee']['salary']) ?>
                              <small>Descontos em folha</small>
                        </div>
                        <div class="badge">
                              <?= __moneyFormatter(($data['employee']['liquid_salary'] + $data['employee']['benefit'])) ?>
                              <small>Salário agregado</small>
                        </div>
                  </div>

                  <!-- Salário -->
                  <div class="card card-salary">
                        <span class="salary-diff <?= $data['employee']['salary_diff']['is_positive']
                                                            ? 'positive'
                                                            : 'negative' ?>">
                              <?= $data['employee']['salary_diff']['formatted'] ?>
                        </span>
                        <p class="salary-amount">
                              <?= __moneyFormatter($data['employee']['liquid_salary']) ?>
                        </p>
                        <p class="salary-label">Salário líquido estimado</p>
                  </div>

                  <!-- Atrasos — 42 min → 42/120 = 35% → dasharray = 35% × 188.5 = 65.975 -->
                  <div class="card card-donut">
                        <div class="donut-wrap">
                              <svg width="80" height="80" viewBox="0 0 80 80">
                                    <circle cx="40" cy="40" r="30" fill="none" stroke="#dbeafe" stroke-width="10" />
                                    <circle cx="40" cy="40" r="30" fill="none" stroke="#3b82c4" stroke-width="10"
                                          stroke-dasharray="65.975 188.5" stroke-linecap="round" />
                              </svg>
                              <div class="donut-center">
                                    <span class="val" style="color:#3b82c4">
                                          <?= strtr($data['totals']['atrasos'], ',', '.') ?>
                                          <?= strpos($data['totals']['atrasos'], ',') !== false ? 'h' : 'm' ?>
                                    </span>
                              </div>
                        </div>
                        <span class="donut-label">Atrasos</span>
                  </div>

                  <!-- Extras — 6.5 h → 6.5/20 = 32.5% → dasharray = 32.5% × 188.5 = 61.26 -->
                  <div class="card card-donut">
                        <div class="donut-wrap">
                              <svg width="80" height="80" viewBox="0 0 80 80">
                                    <circle cx="40" cy="40" r="30" fill="none" stroke="#d1fae5" stroke-width="10" />
                                    <circle cx="40" cy="40" r="30" fill="none" stroke="#2e9e6b" stroke-width="10"
                                          stroke-dasharray="61.26 188.5" stroke-linecap="round" />
                              </svg>
                              <div class="donut-center">
                                    <span class="val" style="color:#2e9e6b">
                                          <?= strtr($data['totals']['extras_50'], ',', '.') ?>
                                          <?= strpos($data['totals']['extras_50'], ',') !== false ? 'h' : 'm' ?>
                                    </span>
                              </div>
                        </div>
                        <span class="donut-label">Extras (50%)</span>
                  </div>

            </div>

            <!-- ── Tabela espelho ────────────────────────────────────────────── -->
            <div class="mirror-card">
                  <div class="mirror-header">
                        <span>Data</span>
                        <span>Entrada</span>
                        <span>Saída Almoço</span>
                        <span>Ret. Almoço</span>
                        <span>Saída</span>
                        <span>Status</span>
                        <span>Saldo</span>
                  </div>
                  <div class="mirror-body">

                        <?php foreach ($data['days'] as $dia): ?>

                              <div class="mirror-row">

                                    <span class="day-col">
                                          <?= $dia['date']; ?>
                                          <small class="day-name">
                                                <?= $dia['week_day']; ?>
                                          </small>
                                    </span>

                                    <span>
                                          <span class="time-pill">
                                                <?= $dia['entrada']; ?>
                                          </span>
                                    </span>

                                    <span>
                                          <span class="time-pill">
                                                <?= $dia['saida_almoco']; ?>
                                          </span>
                                    </span>

                                    <span>
                                          <span class="time-pill">
                                                <?= $dia['volta_almoco']; ?>
                                          </span>
                                    </span>

                                    <span>
                                          <span class="time-pill">
                                                <?= $dia['saida']; ?>
                                          </span>
                                    </span>

                                    <span>
                                          <span class="time-pill  <?= $dia['tipoDia']; ?>">
                                                <?= $dia['tipoDia']; ?>
                                          </span>
                                    </span>

                                    <span>
                                          <span class="saldo  <?= $dia['saldo_type'] ?>">
                                                <?= $dia['saldo']; ?>
                                          </span>
                                    </span>

                              </div>

                        <?php endforeach; ?>

                  </div>
            </div>
      </section>
</body>

</html>