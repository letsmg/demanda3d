@component('mail::message')
# Confirmação de Alteração de Dados Bancários

Uma tentativa de alteração dos dados bancários da loja **{{ $tenantName }}** foi iniciada na plataforma **Demanda3D**.

Se você foi quem solicitou esta alteração, clique no botão abaixo para confirmar:

@component('mail::button', ['url' => $verificationUrl])
Confirmar Alteração
@endcomponent

Se você **não** reconhece esta solicitação, ignore este e-mail. Nenhuma alteração será aplicada sem a confirmação.

Este link expira em 60 minutos.

Obrigado,<br>
Equipe Demanda3D
@endcomponent