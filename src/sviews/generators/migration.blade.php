<?php echo '<?php' ?>

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ShopSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing carts
        Schema::create('{{ $cartTable }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('{{ $userKeyName }}')
                ->on('{{ $usersTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique('user_id');
        });
        // Create table for storing items
        Schema::create('{{ $itemTable }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->bigInteger('cart_id')->unsigned()->nullable();
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->string('sku');
            $table->decimal('price', 20, 2);
            $table->decimal('tax', 20, 2)->default(0);
            $table->decimal('shipping', 20, 2)->default(0);
            $table->string('currency')->nullable();
            $table->integer('quantity')->unsigned();
            $table->string('class')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('{{ $userKeyName }}')
                ->on('{{ $usersTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('cart_id')
                ->references('id')
                ->on('{{ $cartTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique(['sku', 'cart_id']);
            $table->unique(['sku', 'order_id']);
            $table->index(['user_id', 'sku']);
            $table->index(['user_id', 'sku', 'cart_id']);
            $table->index(['user_id', 'sku', 'order_id']);
            $table->index(['reference_id']);
        });
        // Create table for storing coupons
        Schema::create('{{ $couponTable }}', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description', 1024)->nullable();
            $table->string('sku');
            $table->decimal('value', 20, 2)->nullable();
            $table->decimal('discount', 3, 2)->nullable();
            $table->integer('active')->default(1);
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
            $table->index(['code', 'expires_at']);
            $table->index(['code', 'active']);
            $table->index(['code', 'active', 'expires_at']);
            $table->index(['sku']);
        });
        // Create table for storing coupons
        Schema::create('{{ $orderStatusTable }}', function (Blueprint $table) {
            $table->string('code', 32);
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->primary('code');
        });
        // Create table for storing carts
        Schema::create('{{ $orderTable }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('statusCode', 32);
            $table->timestamps();
            $table->foreign('user_id')
                ->references('{{ $userKeyName }}')
                ->on('{{ $usersTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('statusCode')
                ->references('code')
                ->on('{{ $orderStatusTable }}')
                ->onUpdate('cascade');
            $table->index(['user_id', 'statusCode']);
            $table->index(['id', 'user_id', 'statusCode']);
        });
        // Create table for storing transactions
        Schema::create('{{ $transactionTable }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->unsigned();
            $table->string('gateway', 64);
            $table->string('transaction_id', 64);
            $table->string('detail', 1024)->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
            $table->foreign('order_id')
                ->references('id')
                ->on('{{ $orderTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->index(['order_id']);
            $table->index(['gateway', 'transaction_id']);
            $table->index(['order_id', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $transactionTable }}');
        Schema::drop('{{ $orderTable }}');
        Schema::drop('{{ $orderStatusTable }}');
        Schema::drop('{{ $couponTable }}');
        Schema::drop('{{ $itemTable }}');
        Schema::drop('{{ $cartTable }}');
    }
}