<?php


Route::get('/test', function () {

  $faker = Faker\Factory::create();

  echo $faker->text(5);

});
