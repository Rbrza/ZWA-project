<?php
/**
 * Admin overview page: list of insured persons (CSV-backed).
 *
 * Responsibilities:
 * - Loads users from Database.csv (semicolon-delimited)
 * - Sorts users by surname then name (locale-aware when Intl Collator is available)
 * - Paginates the list using query parameters:
 *     - page (int)     : 1-based page number
 *     - per_page (int) : items per page (allowed: 1,5,10,20)
 * - Renders an HTML table with actions:
 *     - Zobrazit (details)
 *     - Upravit (edit)
 *     - Smazat (AJAX via delete-user.php)
 *
 * Security:
 * - Requires login (auth.php)
 * - Requires admin privileges (auth-admin.php)
 *
 * Output:
 * - HTML page. User data is escaped with htmlspecialchars() to prevent XSS.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/auth-admin.php';

$csvPath = __DIR__ . '/Database.csv';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$allowed = array(1, 5, 10, 20);
if (!in_array($perPage, $allowed, true)) $perPage = 10;

// Load CSV
$rows = array();
if (file_exists($csvPath)) {
    $fh = fopen($csvPath, 'r');
    if ($fh) {
        $headers = fgetcsv($fh, 0, ';'); // header row
        while (($r = fgetcsv($fh, 0, ';')) !== false) {
            if (count($r) === count($headers)) {
                $rows[] = array_combine($headers, $r);
            }
        }
        fclose($fh);
    }
}

// Sort rows by surname, then name (PHP 5.6 compatible)
if (class_exists('Collator')) {
    $coll = new Collator('cs_CZ');

    usort($rows, function($a, $b) use ($coll) {
        $sa = isset($a['surname']) ? $a['surname'] : '';
        $sb = isset($b['surname']) ? $b['surname'] : '';

        $cmp = $coll->compare($sa, $sb);
        if ($cmp !== 0) return $cmp;

        $na = isset($a['name']) ? $a['name'] : '';
        $nb = isset($b['name']) ? $b['name'] : '';

        $cmp = $coll->compare($na, $nb);
        if ($cmp !== 0) return $cmp;

        $ia = isset($a['id']) ? (int)$a['id'] : 0;
        $ib = isset($b['id']) ? (int)$b['id'] : 0;

        if ($ia == $ib) return 0;
        return ($ia < $ib) ? -1 : 1;
    });

} else {

    usort($rows, function($a, $b) {
        $sa = mb_strtolower(isset($a['surname']) ? $a['surname'] : '', 'UTF-8');
        $sb = mb_strtolower(isset($b['surname']) ? $b['surname'] : '', 'UTF-8');

        $cmp = strcmp($sa, $sb);
        if ($cmp !== 0) return $cmp;

        $na = mb_strtolower(isset($a['name']) ? $a['name'] : '', 'UTF-8');
        $nb = mb_strtolower(isset($b['name']) ? $b['name'] : '', 'UTF-8');

        $cmp = strcmp($na, $nb);
        if ($cmp !== 0) return $cmp;

        $ia = isset($a['id']) ? (int)$a['id'] : 0;
        $ib = isset($b['id']) ? (int)$b['id'] : 0;

        if ($ia == $ib) return 0;
        return ($ia < $ib) ? -1 : 1;
    });
}



$total = count($rows);
$totalPages = ($total === 0) ? 1 : (int)ceil($total / $perPage);
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$usersPage = array_slice($rows, $offset, $perPage);

/**
 * Builds a query-string for pagination links.
 *
 * @param int $page    1-based page number.
 * @param int $perPage Items per page.
 * @return string URL-encoded query string (without the leading '?').
 */
function qs($page, $perPage)
{
    return 'page=' . urlencode($page) . '&per_page=' . urlencode($perPage);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Správa faktur</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="index.js" defer></script>
</head>
<body>
<?php
require_once __DIR__ . '/header.php';
?>
<main>

    <div class="main-table-container">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th scope="col">Jméno</th>
                    <th scope="col">Příjmení</th>
                    <th scope="col">Datum narození</th>
                    <th scope="col">Email</th>
                    <th scope="col">Telefonní číslo</th>
                </tr>
                </thead>
                <tbody id="main-table-body">
                <?php if (count($usersPage) === 0): ?>
                    <tr>
                        <td colspan="6">Žádné záznamy</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usersPage as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['surname']); ?></td>
                            <td>
                                <time class="js-date" datetime="<?php echo htmlspecialchars($user['DOB']); ?>">
                                    <?php echo htmlspecialchars($user['DOB']); ?>
                                </time>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>

                            <td>
                                <a class="button-20" href="person-details.php?id=<?php echo urlencode($user['id']); ?>">Zobrazit</a>
                                <a class="button-20" href="person-edit.php?id=<?php echo urlencode($user['id']); ?>">Upravit</a>

                                <button
                                        class="button-20 js-delete"
                                        type="button"
                                        data-user-id="<?php echo htmlspecialchars($user['id']); ?>"
                                >Smazat
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="new-person-button-container">
            <a class="button-20" href="register-page.php">Nová osoba</a>
        </div>
        <div class="pagination-container">

            <!-- Prev -->
            <?php if ($page > 1): ?>
                <a class="button-20" href="index.php?<?php echo qs($page - 1, $perPage); ?>">&lt;-</a>
            <?php else: ?>
                <span class="button-20" style="opacity:.5; pointer-events:none;">&lt;-</span>
            <?php endif; ?>

            <?php
            $first = 1;
            $last = $totalPages;

            // window around current page
            $start = max($first, $page - 1);
            $end = min($last, $page + 1);

            // helper to render a page link / current
            $renderPage = function ($p) use ($page, $perPage) {
                if ($p == $page) {
                    echo '<span class="button-20" style="opacity:.7; pointer-events:none;">' . $p . '</span>';
                } else {
                    echo '<a class="button-20" href="index.php?page=' . urlencode($p) . '&per_page=' . urlencode($perPage) . '">' . $p . '</a>';
                }
            };
            ?>

            <!-- First page (only if not already in window) -->
            <?php if ($start > $first): ?>
                <?php $renderPage($first); ?>
                <?php if ($start > $first + 1): ?>
                    <span style="padding:0 8px;">…</span>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Window: prev/current/next -->
            <?php for ($p = $start; $p <= $end; $p++): ?>
                <?php $renderPage($p); ?>
            <?php endfor; ?>

            <!-- Last page (only if not already in window) -->
            <?php if ($end < $last): ?>
                <?php if ($end < $last - 1): ?>
                    <span style="padding:0 8px;">…</span>
                <?php endif; ?>
                <?php $renderPage($last); ?>
            <?php endif; ?>

            <!-- Next -->
            <?php if ($page < $totalPages): ?>
                <a class="button-20" href="index.php?<?php echo qs($page + 1, $perPage); ?>">&gt;</a>
            <?php else: ?>
                <span class="button-20" style="opacity:.5; pointer-events:none;">&gt;</span>
            <?php endif; ?>

            <!-- Per-page -->
            <form method="GET" action="index.php" style="display:inline;">
                <input type="hidden" name="page" value="1">
                <label>
                    Vyberte si počet položek na stránku
                    <select name="per_page" onchange="this.form.submit()">
                        <?php foreach (array(1, 5, 10, 20) as $n): ?>
                            <option value="<?php echo $n; ?>" <?php echo ($n == $perPage) ? 'selected' : ''; ?>>
                                /<?php echo $n; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>

        </div>

    </div>
</main>
<footer>

</footer>
</body>
</html>