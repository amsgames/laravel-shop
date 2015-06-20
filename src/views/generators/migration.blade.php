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
            $table->integer('quantity')->unsigned();
            $table->string('class')->nullable();
            $table->string('description', 1024)->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                ->references('{{ $userKeyName }}')
                ->on('{{ $usersTable }}')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('cart_id')
                ->references('{{ $cartTable }}')
                ->on('id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->unique(['sku', 'cart_id']);
            $table->unique(['sku', 'order_id']);
            $table->index(['user_id', 'sku']);
            $table->index(['user_id', 'sku', 'cart_id']);
            $table->index(['user_id', 'sku', 'order_id']);
            $table->index(['reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $itemTable }}');
        Schema::drop('{{ $cartTable }}');
    }
}
