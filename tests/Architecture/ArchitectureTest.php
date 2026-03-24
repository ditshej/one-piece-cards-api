<?php

arch()
    ->expect('App')
    ->not->toUse(['dd', 'dump']);

arch()
    ->expect('App')
    ->not->toUse(['env']);
