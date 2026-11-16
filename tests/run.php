<?php
spl_autoload_register(function (string $class): void {
    $prefix = 'Djshellshoxxx\\LibreNMSUtilitySuite\\';
    if (! str_starts_with($class, $prefix)) return;
    $path = __DIR__.'/../src/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
    if (is_file($path)) require $path;
});

use Djshellshoxxx\LibreNMSUtilitySuite\Core\ModuleRegistry;
use Djshellshoxxx\LibreNMSUtilitySuite\Core\ConfiguredModule;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\ArraySettingsStore;
use Djshellshoxxx\LibreNMSUtilitySuite\Services\SettingsService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\Bookmarks\BookmarkService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\DeviceTags\TagService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\QrCodes\QrTargetService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\DescriptionAudit\DescriptionAuditService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\DescriptionAudit\DescriptionRuleSet;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortFlaps\PortFlapService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortFlaps\FlapThresholds;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\NotesHistory\DeviceNoteHistoryService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\PortNotes\PortNoteHistoryService;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics\DiagnosticResult;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics\PingParser;
use Djshellshoxxx\LibreNMSUtilitySuite\Modules\Diagnostics\SnmpResultSanitizer;

$tests = [];
$test = function (string $name, callable $fn) use (&$tests) {$tests[$name] = $fn;};
$eq = function ($actual, $expected, string $message = '') {if ($actual !== $expected) throw new RuntimeException(($message ? $message.' ' : '').var_export($actual, true).' !== '.var_export($expected, true));};
$ok = function ($value, string $message = '') {if (! $value) throw new RuntimeException($message ?: 'assertion failed');};

$test('module registry filters and lookup', function () use ($eq) {$s = new SettingsService(new ArraySettingsStore(['modules.a.enabled'=>true,'modules.b.enabled'=>false]));$r = new ModuleRegistry([new ConfiguredModule('a','A',$s),new ConfiguredModule('b','B',$s)]);$eq(count($r->enabled()),1);$eq($r->find('a')->name(),'A');$eq($r->find('x'),null);});
$test('settings normalize booleans', function () use ($eq) {$s = new SettingsService(new ArraySettingsStore(['modules.x.enabled'=>'yes']));$eq($s->moduleEnabled('x'),true);$eq($s->moduleEnabled('missing'),false);});
$test('bookmarks prevent duplicates and isolate users', function () use ($eq) {$s = new BookmarkService();$s->add(1,10);$s->add(1,10);$s->add(2,10);$eq(count($s->forUser(1)),1);$s->remove(1,10);$eq(count($s->forUser(1)),0);$eq(count($s->forUser(2)),1);});
$test('tags normalize duplicates and AND match', function () use ($eq) {$s = new TagService();$a=$s->create(' Critical ');$a2=$s->create('critical');$b=$s->create('Switch');$eq($a['id'],$a2['id']);$s->assign(1,$a['id']);$s->assign(1,$b['id']);$s->assign(2,$a['id']);$eq($s->devicesMatching([$a['id'],$b['id']]),[1]);});
$test('qr targets validate', function () use ($eq) {$s = new QrTargetService();$d=['device_id'=>5,'hostname'=>'sw1','ip'=>'192.0.2.1'];$eq($s->forDevice($d,'librenms',null,'https://nms'),'https://nms/device/device=5/');$eq($s->forDevice($d,'ip'),'192.0.2.1');});
$test('description audit catches blank duplicate and ignores cross-device duplicate', function () use ($eq) {$svc=new DescriptionAuditService();$ports=[['port_id'=>1,'device_id'=>1,'description'=>''],['port_id'=>2,'device_id'=>1,'description'=>'Camera'],['port_id'=>3,'device_id'=>1,'description'=>'Camera'],['port_id'=>4,'device_id'=>2,'description'=>'Camera']];$f=$svc->audit($ports,new DescriptionRuleSet());$eq(array_column($f,'reason'),['blank','duplicate','duplicate']);});
$test('port flap counts only state changes', function () use ($eq) {$s=new PortFlapService();$eq($s->count(['up','up','down','down','up','unknown']),2);$eq($s->severity(26,new FlapThresholds()),'critical');});
$test('device note history ignores unchanged', function () use ($eq) {$s=new DeviceNoteHistoryService();$eq($s->record(1,1,'a','a'),null);$s->record(1,1,'a','b');$eq(count($s->forDevice(1)),1);});
$test('port note history ignores unchanged', function () use ($eq) {$s=new PortNoteHistoryService();$eq($s->record(1,1,1,'a','a'),null);$s->record(1,1,1,'a','b');$eq(count($s->forPort(1)),1);});
$test('ping parser maps packet loss', function () use ($eq) {$p=new PingParser();$r=$p->parse("4 packets transmitted, 4 received, 0% packet loss\nrtt min/avg/max/mdev = 0.7/0.9/1.2/0.1 ms",0);$eq($r->status,'pass');$eq($r->details['avg_ms'],0.9);});
$test('diagnostic sanitizer removes secrets recursively', function () use ($ok) {$s=new SnmpResultSanitizer();$r=$s->sanitize(new DiagnosticResult('fail','bad secret123',['error'=>'secret123 here']),['community'=>'secret123']);$ok(!str_contains($r->summary,'secret123'));$ok(!str_contains($r->details['error'],'secret123'));});
$test('module config defaults expose all eight feature keys', function () use ($eq) {$c=require __DIR__.'/../config/utility-suite.php';$eq(array_keys($c['modules']),['notes_history','bookmarks','port_notes','qr_codes','diagnostics','description_audit','port_flaps','device_tags']);});

$failed = 0;
foreach ($tests as $name => $fn) {
    try {$fn(); echo "PASS $name\n";} catch (Throwable $e) {$failed++; echo "FAIL $name: {$e->getMessage()}\n";}
}
echo "\n".(count($tests)-$failed).'/'.count($tests)." passed\n";
exit($failed ? 1 : 0);
