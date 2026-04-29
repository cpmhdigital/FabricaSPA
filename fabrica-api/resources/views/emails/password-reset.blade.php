<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Recuperação de Senha</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="20" cellspacing="0" style="border: 1px solid #ddd; background-color: #fff;">
                    <tr>
                        <td>
                            <h1 style="color: #007a5a;">Olá, {{ $user->name }}!</h1>

                            <p>Recebemos uma solicitação para redefinir a sua senha.</p>

                            <p>Clique no botão abaixo para criar uma nova senha:</p>

                            <p style="text-align: center;">
                                <a href="{{ $url }}"
                                    style="background-color: #007a5a; color: #fff; padding: 12px 24px;  text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                                    Redefinir Senha
                                </a>
                            </p>

                            <p>Se você não solicitou essa alteração, apenas ignore este e-mail.</p>

                            <hr>

                            <p>Atenciosamente,<br><strong>Sistemas GrupoFix</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
