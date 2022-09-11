@component('mail::message')
  # Link para download do instalador

  Esse é o link para download do instalador da aplicação:
  <br>
  <{{ $installerUrl }}>

  Para instalar: digite ```curl -H "Authorization: Bearer &#60;DISPLAY_API_TOKEN&#62;"$installerUrl``` no terminal do Raspberry

  Obrigado,<br>
  {{ config('app.name') }}
@endcomponent
