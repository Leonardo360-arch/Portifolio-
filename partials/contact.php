<section id="contato" class="container" style="padding-bottom:4rem">
  <h2>Contato</h2>
  <form id="contact-form" class="card admin-form" method="post" action="enviar_contato.php" novalidate>
    <?= csrf_field() ?>
    <label>Nome<input name="nome" maxlength="120" required autocomplete="name"></label>
    <label>E-mail<input type="email" name="email" maxlength="150" required autocomplete="email"></label>
    <label>Assunto (opcional)<input name="assunto" maxlength="150"></label>
    <label>Mensagem<textarea name="mensagem" rows="5" minlength="10" maxlength="5000" required></textarea></label>
    <div style="position:absolute;left:-9999px" aria-hidden="true">
      <label>Não preencha<input name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    <p><button class="btn" type="submit">Enviar mensagem</button></p>
    <p id="contact-status" role="status" aria-live="polite"></p>
  </form>
</section>
<script src="assets/js/contact.js" defer></script>
