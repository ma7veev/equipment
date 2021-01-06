<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
if (php_sapi_name() !== "cli") {
    print "\n\nВиконайте в командному інтерфейсі\n";

    return false;
}
print "Будьласка, введіть значення залишкової вартості S(t), починаючі з базового періоду і відокремлюючи данні по рокам за допомогою Enter\n";
print "Введіть 0+Enter, після завершення заповнення данних\n";

$fp = fopen('php://stdin', 'r');
$last_line = false;
$St = [];
while (!$last_line) {
    $next_line = fgets($fp, 1024);
    if ("0\n" == $next_line) {
        $last_line = true;
    } else {
        $St[] = intval(trim($next_line));
    }
}

print "\n\nЗалишкова вартість S(t) по періодам:\n";
foreach ($St as $key => $value) {
    print "Період $key: $value тис. грн\n";
}

print "\n\nБудьласка, введіть значення доходу L(t), починаючі з базового періоду і відокремлюючи данні по рокам за допомогою Enter\n";
print "Введіть 0+Enter, після завершення заповнення данних\n";
print "Кількість введенних періодів повинна співпадати із попереднім вводом\n";
$fp = fopen('php://stdin', 'r');
$last_line = false;
$Lt = [];
while (!$last_line) {
    $next_line = fgets($fp, 1024);
    if ("0\n" == $next_line) {
        $last_line = true;
    } else {
        $Lt[] = intval(trim($next_line));
    }
}

print "\n\nДоход L(t) по періодам:\n";
foreach ($Lt as $key => $value) {
    print "Період $key: $value тис. грн\n";
}

if (count($Lt) !== count($St)) {
    print "\n\nКількість періодів не співпадає\n";

    return false;
}
$T = count($Lt) - 1;

print "\nДанні періода 0 рахуються як базові. Усього розрахунок проводиться на $T наступніх періодів\n";

print "\n\nУмовна оптимізація:\n";
$P = $St[ 0 ];
$r0 = $Lt[ 0 ];

$cond_opt = [];
$c = 0;

$r = 0;
for ($k = $T; $k >= 1; $k --) {
    $n = $c + 1;
    print "{$n}-ий крок, k = $k\n\n";
    $row = [];
    $r += $Lt[ $c ];
    for ($t = 1; $t <= $k; $t ++) {
        $Ltt = 0;
        for ($i = 0; $i < $n; $i ++) {
            $Ltt += $Lt[ $t + $i ];
        }
        $s = $Ltt;
        $z = ($St[ $t ] - $P) + $r;
        $v = max($s, $z);

        print "Період {$t}: ";
        print "max($Ltt, ($St[$t] - $P) +$r) = $v;";
        if ($s > $z) {
            print " - Зберігаємо\n";
            $row[ $t ] = [ $v, 0 ];
        } else {
            print " - Замінюємо\n";
            $row[ $t ] = [ $v, 1 ];
        }
    }
    print "\n\n";
    $cond_opt[ $k ] = $row;
    $c ++;
}

print "\n\nБезумовна оптимізація:\n";
$t = 1;

asort($cond_opt);
$uncond_opt = [];
foreach ($cond_opt as $k => $s) {
    print "k = $k\n";
    print "Вік обладнання $t років\n";
    if ($s[ $t ][ 1 ] == 1) {
        print "Замінюємо\n\n";
        $uncond_opt[] = $k;
        $t = 0;
    } else {
        print "Зберігаємо\n\n";
    }

    $t ++;
}
print "Таким чином заміну обладнання за $T років експлуатаціі треба прободити на початку " . implode(", ", $uncond_opt) . " року\n";
