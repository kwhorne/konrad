<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds FULLTEXT index for efficient search on contacts table.
     * This significantly improves search performance compared to LIKE %...%.
     */
    public function up(): void
    {
        // Only add FULLTEXT index for MySQL/MariaDB
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // Check if index already exists
            $indexes = DB::select("SHOW INDEX FROM contacts WHERE Key_name = 'contacts_search_fulltext'");

            if (empty($indexes)) {
                DB::statement('ALTER TABLE contacts ADD FULLTEXT INDEX contacts_search_fulltext (company_name, email, organization_number)');
            }

            // Also add regular indexes for prefix searches on contact_number
            $contactNumberIndex = DB::select("SHOW INDEX FROM contacts WHERE Key_name = 'contacts_contact_number_index'");
            if (empty($contactNumberIndex)) {
                DB::statement('ALTER TABLE contacts ADD INDEX contacts_contact_number_index (contact_number)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            // Check if FULLTEXT index exists before dropping
            $indexes = DB::select("SHOW INDEX FROM contacts WHERE Key_name = 'contacts_search_fulltext'");
            if (! empty($indexes)) {
                DB::statement('ALTER TABLE contacts DROP INDEX contacts_search_fulltext');
            }

            // Drop contact_number index
            $contactNumberIndex = DB::select("SHOW INDEX FROM contacts WHERE Key_name = 'contacts_contact_number_index'");
            if (! empty($contactNumberIndex)) {
                DB::statement('ALTER TABLE contacts DROP INDEX contacts_contact_number_index');
            }
        }
    }
};
