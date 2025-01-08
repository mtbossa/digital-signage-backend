@component('mail::message')
  # Convite para instância paroquia-pio-x

  Você foi convidado para participar da instância da paroquia-pio-x.
  Para aceitar, clique no botão abaixo ou no link caso o botão não esteja funcionando:<br><{{ $url }}>

  @component('mail::button', ['url' => $url])
    Aceitar convite
  @endcomponent

  Obrigado,<br>
  {{ config('app.name') }}
@endcomponent
