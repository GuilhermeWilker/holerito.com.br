<!DOCTYPE html>
<html lang="pt-BR">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Holerito — Autenticação</title>
      <link rel="stylesheet" href="/assets/css/app.css">
      <link rel="shortcut icon" href="/assets/icon.png" type="image/x-icon">
      <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
      <style>
            .auth-page {
                  min-height: 100vh;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  padding: 1.5rem;
            }

            .auth-card {
                  width: 100%;
                  max-width: 440px;
                  background: var(--card-bg, #fff);
                  border: 1px solid var(--border, #e5e7eb);
                  border-radius: 16px;
                  padding: 2.5rem 2rem;
                  display: flex;
                  flex-direction: column;
                  gap: 2rem;
            }

            .auth-header {
                  display: flex;
                  flex-direction: column;
                  gap: 0.4rem;
            }

            .auth-brand {
                  font-family: 'DM Mono', monospace;
                  font-size: 0.72rem;
                  font-weight: 500;
                  letter-spacing: 0.12em;
                  text-transform: uppercase;
                  color: var(--muted, #9ca3af);
            }

            .auth-title {
                  font-family: 'DM Mono', monospace;
                  font-size: 1.1rem;
                  font-weight: 500;
                  color: var(--text, #111827);
                  line-height: 1.3;
            }

            .auth-title span {
                  color: var(--accent, #3b82c4);
            }

            .auth-desc {
                  font-family: 'Sora', sans-serif;
                  font-size: 0.8rem;
                  font-weight: 300;
                  color: var(--muted, #9ca3af);
                  margin-top: 0.25rem;
                  line-height: 1.6;
            }

            .auth-fields {
                  display: flex;
                  flex-direction: column;
                  gap: 1.25rem;
            }

            .field-group {
                  display: flex;
                  flex-direction: column;
                  gap: 0.4rem;
            }

            .field-label {
                  font-family: 'DM Mono', monospace;
                  font-size: 0.7rem;
                  font-weight: 500;
                  letter-spacing: 0.08em;
                  color: var(--muted, #9ca3af);
                  text-transform: uppercase;
            }

            .field-input {
                  font-family: 'DM Mono', monospace;
                  font-size: 0.78rem;
                  padding: 0.65rem 0.9rem;
                  border: 1px solid var(--border, #e5e7eb);
                  border-radius: 8px;
                  background: var(--input-bg, #f9fafb);
                  color: var(--text, #111827);
                  outline: none;
                  transition: border-color 0.15s, box-shadow 0.15s;
                  width: 100%;
                  box-sizing: border-box;
            }

            .field-input:focus {
                  border-color: var(--accent, #3b82c4);
                  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent, #3b82c4) 12%, transparent);
            }

            .field-input::placeholder {
                  color: var(--muted, #9ca3af);
                  opacity: 0.6;
            }

            .auth-submit {
                  font-family: 'Sora', sans-serif;
                  font-size: 0.85rem;
                  font-weight: 500;
                  padding: 0.75rem 1rem;
                  border-radius: 8px;
                  border: none;
                  background: var(--accent, #3b82c4);
                  color: #fff;
                  cursor: pointer;
                  width: 100%;
                  transition: opacity 0.15s;
            }

            .auth-submit:hover {
                  opacity: 0.88;
            }

            .auth-notice {
                  font-family: 'Sora', sans-serif;
                  font-size: 0.72rem;
                  font-weight: 300;
                  color: var(--muted, #9ca3af);
                  text-align: center;
                  line-height: 1.6;
            }

            <?php if (isset($error)): ?>.auth-error {
                  font-family: 'DM Mono', monospace;
                  font-size: 0.75rem;
                  background: #fef2f2;
                  border: 1px solid #fecaca;
                  color: #dc2626;
                  border-radius: 8px;
                  padding: 0.65rem 0.9rem;
            }

            <?php endif; ?>
      </style>
</head>

<body>
      <section class="auth-page">
            <div class="auth-card">

                  <div class="auth-header">
                        <span class="auth-brand">Holerito</span>
                        <h1 class="auth-title">
                              <span>session</span>_cookies
                        </h1>
                        <p class="auth-desc">
                              Cole os cookies do Portal RH para autenticar a sessão.<br>
                              Abra o DevTools → Application → Cookies.
                        </p>
                  </div>

                  <?php if (isset($error)): ?>
                        <div class="auth-error"><?= htmlspecialchars($error) ?></div>
                  <?php endif; ?>

                  <form action="/auth" method="post" class="auth-fields">

                        <div class="field-group">
                              <label class="field-label" for="RequestVerificationToken">
                                    __RequestVerificationToken
                              </label>
                              <input
                                    class="field-input"
                                    type="text"
                                    id="RequestVerificationToken"
                                    name="RequestVerificationToken"
                                    placeholder="Cole o token aqui"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars($_SESSION['RequestVerificationToken'] ?? '') ?>" />
                        </div>

                        <div class="field-group">
                              <label class="field-label" for="ASPNET_SessionId">
                                    ASP.NET_SessionId
                              </label>
                              <input
                                    class="field-input"
                                    type="text"
                                    id="ASPNET_SessionId"
                                    name="ASPNET_SessionId"
                                    placeholder="Cole o session ID aqui"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars($_SESSION['ASPNET_SessionId'] ?? '') ?>" />
                        </div>

                        <div class="field-group">
                              <label class="field-label" for="ASPXAUTH">
                                    .ASPXAUTH
                              </label>
                              <input
                                    class="field-input"
                                    type="text"
                                    id="ASPXAUTH"
                                    name="ASPXAUTH"
                                    placeholder="Cole o token de autenticação aqui"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars($_SESSION['ASPXAUTH'] ?? '') ?>" />
                        </div>

                        <button type="submit" class="auth-submit">
                              Salvar e entrar
                        </button>

                  </form>

                  <p class="auth-notice">
                        Os cookies ficam armazenados apenas na sua sessão<br>e são apagados ao fechar o navegador.
                  </p>

            </div>
      </section>
</body>

</html>