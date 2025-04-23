#!/usr/bin/env php
<?php

echo "🚀 MicroApp Init Starting...\n";

$base = __DIR__;

echo "🔧 Injecting autoload...\n";
passthru("php $base/init-autoload.php");

echo "🔧 Setting up .htaccess...\n";
passthru("php $base/init-htaccess.php");

echo "⚙️ Scaffolding index.php...\n";
passthru("php $base/init-index.php");

echo "✅ MicroApp Init Complete.\n";
