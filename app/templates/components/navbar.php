           <?php

            $currentRoute = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $navigation = [
                  '/' => [
                        [
                              'label' => 'Cartão de crédito X Salário',
                              'href' => '/invoice-creditcard',
                        ],
                        [
                              'label' => 'Faturas',
                              'href' => '/invoices',
                        ]
                  ],

                  '/invoice-creditcard' => [
                        [
                              'label' => 'Espelho de Ponto',
                              'href' => '/',
                        ],
                        [
                              'label' => 'Faturas',
                              'href' => '/invoices',
                        ]
                  ],

                  '/invoices' => [
                        [
                              'label' => 'Espelho de Ponto',
                              'href' => '/',
                        ],
                        [
                              'label' => 'Cartão de crédito X Salário',
                              'href' => '/invoice-creditcard',
                        ],
                  ]
            ];

            $links = $navigation[$currentRoute] ?? [];
            ?>

           <header class="topbar">
                 <div class="nav-links">

                       <?php foreach ($links as $link): ?>
                             <a href="<?= $link['href'] ?>">
                                   <?= $link['label'] ?>
                             </a>
                       <?php endforeach; ?>

                       <?php if ($currentRoute === '/'): ?>
                             <form action="/add/payload" method="post">
                                   <select name="payload" class="input" onchange="this.form.submit()">
                                         <?php foreach ($payloads as $label => $value): ?>
                                               <option value="<?= $value ?>" <?= $value === $currentPayload ? 'selected' : '' ?>>
                                                     <?= $label ?>
                                               </option>
                                         <?php endforeach; ?>
                                   </select>
                             </form>
                       <?php endif; ?>

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