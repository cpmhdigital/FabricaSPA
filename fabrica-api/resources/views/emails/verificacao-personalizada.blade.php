<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Bem-vindo ao sistema!</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="600" cellpadding="20" cellspacing="0" style="border: 1px solid #ddd; background-color: #fff;">
                    <tr>
                        <td>
                            <h1 style="color: #007a5a;">Olá, {{ $user->name }}!</h1>

                            <p>Seja bem-vindo(a) ao sistema da <strong>Fábrica</strong>.</p>

                            <p>Você criou uma conta em nosso sistema. Clique no botão abaixo para confirmar seu endereço de e-mail:</p>

                            <p style="text-align: center;">
                                <a href="{{ $url }}"
                                    style="background-color: #007a5a; color: #fff; padding: 12px 24px;  text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                                    Confirmar e-mail
                                </a>
                            </p>
                            <p style="color: #dc3545;">Lembre-se: Após a confirmação, seu cadastro será validado pelo Administrador para o 1º acesso.</p>

                            <hr>

                            <p>Se você não criou esta conta, ignore este e-mail.</p>

                            <p>Atenciosamente,<br><strong>Sistemas GrupoFix</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
