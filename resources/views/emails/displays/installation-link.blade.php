@component('mail::message')
  # Link para download do instalador

  Esse é o link para download do instalador da aplicação:
  <br>
  <{{ $installerUrl }}>

  Para instalar: digite ```test``` no terminal

  Obrigado,<br>
  {{ config('app.name') }}
@endcomponent
