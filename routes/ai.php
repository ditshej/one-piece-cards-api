<?php

use App\Mcp\Servers\CardsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', CardsServer::class);
