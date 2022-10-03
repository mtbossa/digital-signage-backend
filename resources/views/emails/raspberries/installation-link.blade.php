@component('mail::message')
  # Link para instalação

  Esse é o link para download do instalador da aplicação:
  <br>
  <{{ $installerUrl }}>

  Para instalar: digite <br>
  **`sudo curl -H "Authorization: Bearer
  <RASPBERRY_API_TOKEN>" {{ $installerUrl }} | bash`** <br>
    no terminal do Raspberry

  {{ config('app.name') }}
@endcomponent
