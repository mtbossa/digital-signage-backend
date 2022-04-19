@component('mail::message')
  # Convite para instância Intus

  Você foi convidado para participar da instância da Intus.
  Para aceitar, clique no botão abaixo ou no link caso o botão não esteja funcionando:<br><{{ $url }}>

  @component('mail::button', ['url' => $url])
    Aceitar convite
  @endcomponent

  Obrigado,<br>
  {{ config('app.name') }}
@endcomponent
