<!doctype html>
<html lang="pt-PT" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Nova conta criada</title>
    <style type="text/css">
      html,
      body {
        margin: 0 !important;
        padding: 0 !important;
        height: 100% !important;
        width: 100% !important;
      }
      * {
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
      }
      table,
      td {
        mso-table-lspace: 0pt !important;
        mso-table-rspace: 0pt !important;
      }
      table {
        border-spacing: 0 !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
        margin: 0 auto !important;
      }
      img {
        -ms-interpolation-mode: bicubic;
      }
      a {
        text-decoration: none;
      }

      @media only screen and (max-width: 620px) {
        .email-container {
          width: 100% !important;
          max-width: 100% !important;
        }
        .mobile-padding {
          padding-left: 20px !important;
          padding-right: 20px !important;
        }
        h1 {
          font-size: 24px !important;
          line-height: 30px !important;
        }
        .button-a {
          width: 100% !important;
          max-width: 100% !important;
        }
      }
    </style>
  </head>
  <body width="100%" style="margin: 0; padding: 0 !important; background-color: #f5f5f5">
    <center style="width: 100%; background-color: #f5f5f5">
      <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all">
        A sua conta foi criada com sucesso! {{$user->name}}
      </div>

      <div style="max-height: 40px; font-size: 40px; line-height: 40px">&nbsp;</div>

      <table
        align="center"
        role="presentation"
        cellspacing="0"
        cellpadding="0"
        border="0"
        width="600"
        style="margin: auto; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05)"
        class="email-container"
      >
        <!-- HEADER -->
        <tr>
          <td style="padding: 50px 40px 40px 40px; text-align: center; border-bottom: 1px solid #f0f0f0" class="mobile-padding">
            <!-- Logo -->
            {{-- <img
              src="{{logo_url}}"
              alt="Oficina do Futuro"
              width="180"
              style="display: block; margin: 0 auto 30px auto; max-width: 180px; height: auto"
            /> --}}

            <h1
              style="
                margin: 30px 0 12px 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 26px;
                line-height: 32px;
                font-weight: 600;
                color: #1a1a1a;
              "
            >
              Nova Conta Criada!
            </h1>
            <p
              style="
                margin: 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 15px;
                line-height: 22px;
                color: #666666;
              "
            >
              Obrigado por se juntar a nós!
            </p>
          </td>
        </tr>

        <!-- BODY -->
        <tr>
          <td style="padding: 40px" class="mobile-padding">
            <p
              style="
                margin: 0 0 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 16px;
                line-height: 24px;
                color: #1a1a1a;
              "
            >
              Olá, <strong>{{$user->name}}</strong>!
            </p>

            <p
              style="
                margin: 0 0 30px 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 15px;
                line-height: 26px;
                color: #444444;
              "
            >
              A sua conta foi criada com sucesso! Estamos entusiasmados por tê-lo connosco. Para aceder ao sistema por favor use as seguintes credenciais: <br>
              <strong>Email:</strong> {{$user->email}} <br>
              <strong>Password:</strong> {{$password}}
            </p>

            <!-- Details Card -->
            <table
              role="presentation"
              cellspacing="0"
              cellpadding="0"
              border="0"
              width="100%"
              style="margin: 0 0 35px 0; background-color: #fafafa; border-radius: 8px; border: 1px solid #eee"
            >
              <tr>
                <td style="padding: 24px">
                  <p
                    style="
                      margin: 0 0 16px 0;
                      font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                      font-size: 12px;
                      font-weight: 600;
                      color: #888888;
                      text-transform: uppercase;
                      letter-spacing: 1px;
                    "
                  >
                    Detalhes do Usuário
                  </p>

                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #888888;
                        "
                      >
                        Nome
                      </td>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          text-align: right;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #1a1a1a;
                          font-weight: 500;
                        "
                      >
                        {{$user->name}}
                      </td>
                    </tr>
                    <tr>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #888888;
                        "
                      >
                        Email
                      </td>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          text-align: right;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #1a1a1a;
                          font-weight: 500;
                        "
                      >
                        {{$user->email}}
                      </td>
                    </tr>
                    <tr>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #888888;
                        "
                      >
                        Data de criação
                      </td>
                      <td
                        style="
                          padding: 12px 0;
                          border-bottom: 1px solid #eee;
                          text-align: right;
                          font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                          font-size: 14px;
                          color: #1a1a1a;
                          font-weight: 600;
                        "
                      >
                        {{$user->created_at}}
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="padding: 30px 40px; text-align: center; border-top: 1px solid #f0f0f0" class="mobile-padding">
            <p
              style="
                margin: 0 0 10px 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 13px;
                line-height: 20px;
                color: #888888;
              "
            >
              Precisa de ajuda? Envie-nos um email para<br />
              <a href="mailto:info@kriative.com.mz" style="color: #1a1a1a; font-weight: 500">info@kriative.com.mz</a>
            </p>
          </td>
        </tr>

        <!-- COPYRIGHT -->
        <tr>
          <td
            style="padding: 20px 40px 30px 40px; text-align: center; background-color: #fafafa; border-radius: 0 0 12px 12px"
            class="mobile-padding"
          >
            <p
              style="
                margin: 0;
                font-family: -apple-system, BlinkMacSystemFont, &quot;Segoe UI&quot;, Roboto, Helvetica, Arial, sans-serif;
                font-size: 12px;
                line-height: 18px;
                color: #999999;
              "
            >
              © 2026 Kriative. Todos os direitos reservados.
            </p>
          </td>
        </tr>
      </table>

      <div style="max-height: 40px; font-size: 40px; line-height: 40px">&nbsp;</div>
    </center>
  </body>
</html>
