<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempExcelTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('temp_excel_table', function (Blueprint $table) {
            $table->id();
            $table->string('CODIGO_SEDE', 255);
            $table->string('SEDE', 255);
            $table->string('CODIGO_REGIONAL', 255);
            $table->string('REGIONAL', 255);
            $table->string('FICHA', 255);
            $table->string('ESTADO_FICHA', 255);
            $table->string('CODIGO_PROGRAMA', 255);
            $table->string('VERSION_PROGRANA', 255);
            $table->string('PROGRAMA', 255);
            $table->string('NIVEL_DE_FORMACION', 255);
            $table->string('TIPO_DOCUMENTO', 255);
            $table->string('NUMERO_DOCUMENTO', 255);
            $table->string('NOMBRE', 255);
            $table->string('PRIMER_APELLIDO', 255);
            $table->string('SEGUNDO_APELLIDO', 255);
            $table->string('ESTADO_APRENDIZ', 255);
            $table->string('IDENTIFICADOR_CONVENIO', 255);
            $table->string('AMPLIACION_COVERTURA', 255);
            $table->string('CONVENIO', 255);
            $table->string('TIPO_DOCUMENTO_EMPRESA', 255);
            $table->string('NUMERO_DOCUMENTO_EMPRESA', 255);
            $table->string('EMPRESA', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('temp_excel_table');
    }
}
