<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('status', 'users_status_index');
            if (Schema::hasColumn('users', 'sales_rep_id')) {
                $table->index('sales_rep_id', 'users_sales_rep_id_index');
            }
        });

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->index('status', 'parent_orders_status_index');
            $table->index('user_id', 'parent_orders_user_id_index');
            if (Schema::hasColumn('parent_orders', 'sales_rep_id')) {
                $table->index('sales_rep_id', 'parent_orders_sales_rep_id_index');
            }
            $table->index(['is_archived', 'batch_id', 'status'], 'parent_orders_archived_batch_status_idx');
        });

        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->index('status', 'quote_requests_status_index');
                $table->index('created_at', 'quote_requests_created_at_index');
            });
        }

        if (Schema::hasTable('news_articles')) {
            Schema::table('news_articles', function (Blueprint $table) {
                $table->index(['is_active', 'sort_order', 'published_at'], 'news_articles_active_sort_pub_idx');
            });
        }

        if (Schema::hasTable('testimonials')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->index(['is_active', 'sort_order'], 'testimonials_active_sort_idx');
            });
        }

        if (Schema::hasTable('landing_collections')) {
            Schema::table('landing_collections', function (Blueprint $table) {
                $table->index(['is_active', 'sort_order'], 'landing_col_active_sort_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('landing_collections')) {
            Schema::table('landing_collections', function (Blueprint $table) {
                $table->dropIndex('landing_col_active_sort_idx');
            });
        }

        if (Schema::hasTable('testimonials')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->dropIndex('testimonials_active_sort_idx');
            });
        }

        if (Schema::hasTable('news_articles')) {
            Schema::table('news_articles', function (Blueprint $table) {
                $table->dropIndex('news_articles_active_sort_pub_idx');
            });
        }

        if (Schema::hasTable('quote_requests')) {
            Schema::table('quote_requests', function (Blueprint $table) {
                $table->dropIndex('quote_requests_status_index');
                $table->dropIndex('quote_requests_created_at_index');
            });
        }

        Schema::table('parent_orders', function (Blueprint $table) {
            $table->dropIndex('parent_orders_status_index');
            $table->dropIndex('parent_orders_user_id_index');
            if (Schema::hasColumn('parent_orders', 'sales_rep_id')) {
                $table->dropIndex('parent_orders_sales_rep_id_index');
            }
            $table->dropIndex('parent_orders_archived_batch_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_status_index');
            if (Schema::hasColumn('users', 'sales_rep_id')) {
                $table->dropIndex('users_sales_rep_id_index');
            }
        });
    }
};
