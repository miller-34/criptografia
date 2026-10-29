<form method="post" class="auth-form vault-form">
    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

    <label for="service_name">Serviço</label>
    <input id="service_name" name="service_name" maxlength="120" value="<?= escape($formData['service_name']) ?>" placeholder="Ex.: E-mail, banco ou rede social" required autofocus>

    <label for="website_url">Site <span class="optional">opcional</span></label>
    <input id="website_url" name="website_url" type="url" maxlength="2048" value="<?= escape($formData['website_url']) ?>" placeholder="https://exemplo.com">

    <label for="username">Usuário ou e-mail <span class="optional">opcional</span></label>
    <input id="username" name="username" maxlength="254" autocomplete="off" value="<?= escape($formData['username']) ?>">

    <label for="password">
        Senha
        <?php if (!$passwordRequired): ?><span class="optional">deixe em branco para manter</span><?php endif; ?>
    </label>
    <div class="password-field">
        <input id="password" name="password" type="password" maxlength="4096" autocomplete="new-password" <?= $passwordRequired ? 'required' : '' ?>>
        <button class="field-button" type="button" data-toggle-password="password" aria-label="Mostrar ou ocultar senha">Mostrar</button>
    </div>

    <label for="notes">Observações <span class="optional">opcional</span></label>
    <textarea id="notes" name="notes" maxlength="4000" rows="4" placeholder="Códigos de recuperação ou outras informações"><?= escape($formData['notes']) ?></textarea>

    <button type="submit"><?= escape($submitLabel) ?></button>
</form>
