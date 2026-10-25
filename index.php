<?php
$config = file_exists(__DIR__ . '/config.php')
    ? require __DIR__ . '/config.php'
    : die('config.php belum ada. Salin config.example.php menjadi config.php dan isi API key.');

function apiGet(string $url, array $params, string $apiKey): array
{
    $ch = curl_init($url . '?' . http_build_query($params));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'X-Api-Key: ' . $apiKey,
            'Accept: application/json',
        ],
        // NewsAPI menolak request tanpa User-Agent
        CURLOPT_USERAGENT      => 'NewsRestClient/1.0',
    ]);

    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return ['status' => 'error', 'message' => "cURL error: $error"];
    }
    $data = json_decode($body, true) ?? [];
    $data['http_code'] = $status;
    return $data;
}

// Ambil input dari form
$country  = $_GET['country']  ?? 'us';
$category = $_GET['category'] ?? '';
$q        = trim($_GET['q'] ?? '');

$params = ['country' => $country, 'pageSize' => 12];
if ($category !== '') $params['category'] = $category;
if ($q !== '')        $params['q'] = $q;

$result   = apiGet($config['base_url'] . '/top-headlines', $params, $config['api_key']);
$articles = $result['articles'] ?? [];
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>News REST Client</title>
<style>
  body { font-family: system-ui, sans-serif; max-width: 960px; margin: 2rem auto; padding: 0 1rem; background: #f5f5f5; }
  form { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
  input, select, button { padding: .5rem; border: 1px solid #ccc; border-radius: 6px; }
  button { background: #2b6cb0; color: #fff; border: 0; cursor: pointer; }
  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
  .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
  .card img { width: 100%; height: 160px; object-fit: cover; }
  .card div { padding: .8rem; }
  .card small { color: #666; }
  .error { background: #fed7d7; padding: 1rem; border-radius: 6px; }
</style>
</head>
<body>
<h1>News REST Client</h1>

<form method="get">
  <input name="q" placeholder="Kata kunci..." value="<?= $e($q) ?>">
  <select name="country">
    <?php foreach (['us' => 'Amerika', 'id' => 'Indonesia', 'gb' => 'Inggris', 'au' => 'Australia'] as $k => $v): ?>
      <option value="<?= $k ?>" <?= $country === $k ? 'selected' : '' ?>><?= $v ?></option>
    <?php endforeach; ?>
  </select>
  <select name="category">
    <option value="">Semua kategori</option>
    <?php foreach (['business','entertainment','health','science','sports','technology'] as $c): ?>
      <option value="<?= $c ?>" <?= $category === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Cari</button>
</form>

<?php if (($result['status'] ?? '') !== 'ok'): ?>
  <div class="error">
    <strong>Request gagal (HTTP <?= $e($result['http_code'] ?? '-') ?>)</strong><br>
    <?= $e($result['message'] ?? 'Tidak diketahui') ?>
  </div>
<?php elseif (!$articles): ?>
  <p>Tidak ada berita ditemukan.</p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($articles as $a): ?>
      <a class="card" href="<?= $e($a['url']) ?>" target="_blank" rel="noopener" style="text-decoration:none;color:inherit">
        <?php if (!empty($a['urlToImage'])): ?><img src="<?= $e($a['urlToImage']) ?>" alt=""><?php endif; ?>
        <div>
          <strong><?= $e($a['title']) ?></strong><br>
          <small><?= $e($a['source']['name'] ?? '') ?> · <?= $e(substr($a['publishedAt'] ?? '', 0, 10)) ?></small>
          <p><?= $e($a['description'] ?? '') ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</body>
</html>