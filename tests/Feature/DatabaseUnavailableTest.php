<?php

namespace Tests\Feature;

use App\Support\DatabaseStatus;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use PDO;
use PDOException;
use Tests\TestCase;

/**
 * Owner, 2026-11-23: when the database is off (XAMPP's MySQL stopped, wrong login, database missing) visitors get a
 * plain "Server error" page (503) with only a "Try again" button, never the word "database" or the technical PDO / SQL
 * error, even with APP_DEBUG on.
 */
class DatabaseUnavailableTest extends TestCase
{
    /** A PDOException as PDO raises it when MySQL is not running. */
    private function refused(): PDOException
    {
        $e = new PDOException('SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it');
        $e->errorInfo = ['HY000', 2002, 'No connection could be made because the target machine actively refused it'];

        return $e;
    }

    public function test_connection_failures_are_recognised_and_query_bugs_are_not(): void
    {
        $this->assertTrue(DatabaseStatus::isUnavailable($this->refused()));
        $this->assertSame(2002, DatabaseStatus::driverCode($this->refused()));
        $this->assertTrue(DatabaseStatus::isUnavailable(new PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'")));
        $this->assertTrue(DatabaseStatus::isUnavailable(new PDOException("SQLSTATE[HY000] [1049] Unknown database 'bakr'")));
        $this->assertTrue(DatabaseStatus::isUnavailable(new QueryException('mysql', 'select 1', [], $this->refused())));

        // A mistake in a query is a real bug: it keeps the normal error handling.
        $this->assertFalse(DatabaseStatus::isUnavailable(new QueryException('mysql', 'select * from nope', [], new PDOException("SQLSTATE[42S02]: Base table or view not found: 1146 Table 'bakr.nope' doesn't exist"))));
        $this->assertFalse(DatabaseStatus::isUnavailable(new \RuntimeException('Something else')));
    }

    public function test_a_lost_connection_during_a_page_shows_the_friendly_page(): void
    {
        config(['app.debug' => true]);   // even in debug mode: no technical details
        Route::middleware('web')->get('/_db-test', fn () => throw new QueryException('mysql', 'select * from users where id = ?', [1], $this->refused()));

        $this->get('/_db-test')->assertStatus(503)
            ->assertHeader('Retry-After', (string) DatabaseStatus::RETRY_AFTER)
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertSee('Server error')->assertSee('BAKR is having a server problem at the moment. Please try again in a few minutes.')->assertSee('Try again')
            ->assertDontSee('database', false)->assertDontSee('Database', false)->assertDontSee('Home page')
            ->assertDontSee('SQLSTATE')->assertDontSee('select * from users')->assertDontSee('actively refused');

        $this->getJson('/_db-test')->assertStatus(503)->assertJson(['message' => 'BAKR is having a server problem at the moment. Please try again in a few minutes.'])
            ->assertDontSee('SQLSTATE');
    }

    public function test_mysql_off_is_caught_by_pdo_before_the_page_starts(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql is not installed.');
        }
        config(['app.debug' => true]);
        // A real PDO connection to a MySQL port where nothing listens (as when XAMPP's MySQL is stopped).
        config(['database.default' => 'offline', 'database.connections.offline' => [
            'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 1, 'database' => 'bakr', 'username' => 'root', 'password' => '',
            'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'options' => [PDO::ATTR_TIMEOUT => 2],
        ]]);

        $html = $this->get('/')->assertStatus(503)->assertSee('Server error')->assertDontSee('database', false)->assertDontSee('SQLSTATE')->assertDontSee('127.0.0.1');
        $html = $html->getContent();
        $this->assertSame(1, substr_count($html, '<a '));   // only "Try again"
        $this->assertStringContainsString('Try again</a>', $html);
        $this->get('/scholarLogin')->assertStatus(503)->assertSee('Server error');
    }
}
