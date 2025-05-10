<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// A1:2017 - Injection vulnerability - Direct SQL query without prepared statements
$query = "SELECT t.*, 
          (SELECT account_number FROM accounts WHERE account_number = t.from_account) as from_acc,
          (SELECT account_number FROM accounts WHERE account_number = t.to_account) as to_acc
          FROM transactions t
          WHERE t.from_account IN (SELECT account_number FROM accounts WHERE user_id = $user_id)
          OR t.to_account IN (SELECT account_number FROM accounts WHERE user_id = $user_id)
          ORDER BY t.created_at DESC";

$transactions = mysqli_query($conn, $query);

require_once 'header.php';
?>

<div class="statement-page">
    <div class="page-header">
        <h1>Transaction History</h1>
        <p class="subtitle">View all your recent transactions</p>
    </div>

    <div class="statement-card">
        <div class="statement-filters">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search transactions...">
            </div>
        </div>

        <div class="transactions-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From Account</th>
                        <th>To Account</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaction = mysqli_fetch_assoc($transactions)): ?>
                    <tr>
                        <td class="date-cell"><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                        <td class="account-cell"><?php echo $transaction['from_acc']; ?></td>
                        <td class="account-cell"><?php echo $transaction['to_acc']; ?></td>
                        <td class="amount-cell">$<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td class="description-cell"><?php echo $transaction['description']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .statement-page {
        padding: 2rem 0;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .subtitle {
        color: var(--text-light);
        font-size: 1.1rem;
    }

    .statement-card {
        background: var(--surface);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    .statement-filters {
        margin-bottom: 1.5rem;
    }

    .search-box input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .transactions-table {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    th {
        background: var(--background);
        color: var(--text);
        font-weight: 600;
        text-align: left;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: var(--text);
    }

    tr:hover {
        background: var(--background);
    }

    .date-cell {
        white-space: nowrap;
        color: var(--text-light);
    }

    .account-cell {
        font-family: monospace;
        font-size: 0.9rem;
    }

    .amount-cell {
        font-weight: 600;
        color: var(--primary);
    }

    .description-cell {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .statement-page {
            padding: 1rem 0;
        }

        .page-header h1 {
            font-size: 1.5rem;
        }

        .statement-card {
            padding: 1rem;
        }

        td, th {
            padding: 0.75rem;
        }
    }
</style>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const table = document.querySelector('table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                    found = true;
                    break;
                }
            }

            row.style.display = found ? '' : 'none';
        }
    });
</script>

<?php require_once 'footer.php'; ?> 