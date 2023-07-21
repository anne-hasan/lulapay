<?php namespace Lulapay\PaymentGateway\Models;

use Model;

/**
 * Model
 */
class Provider extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'lulapay_paymentgateway_providers';

    /**
     * @var array Validation rules
     */

    public $rules = [
        'name' => 'required|between:4,12|unique:lulapay_paymentgateway_providers',
        'code' => 'required|between:4,12|unique:lulapay_paymentgateway_providers',
    ];

    public function get_detail()
    {
        return $this;
    }

    public function save_data()
    {
        $this->save();
    }

    public function update_data()
    {
        $this->save();
    }
    
    public function delete_data()
    {
        $this->delete();
    }

}
