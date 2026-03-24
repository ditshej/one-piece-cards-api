<?php

arch()
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'env']);
