<?php
$n = 1000;
$start = microtime(true);
for ($i = 0; $i < $n; $i++) {
    // simulating network roundtrip latency (e.g., 1ms)
    usleep(1000);
}
$time_n1 = microtime(true) - $start;

$start = microtime(true);
// simulating 1 network roundtrip
usleep(1000);
$time_in = microtime(true) - $start;

echo "N+1 Queries Time: {$time_n1}s\n";
echo "IN Clause Time: {$time_in}s\n";
echo "Improvement: " . round($time_n1 / $time_in, 2) . "x\n";
