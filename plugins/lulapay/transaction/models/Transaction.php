<?php namespace Lulapay\Transaction\Models;

use Lulapay\Transaction\Models\TransactionLog;
use Model;
use Ramsey\Uuid\Uuid;

/**
 * Model
 */
class Transaction extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public $fillable = [
        'invoice_code',
        'merchant_id',
        'customer_id',
        'payment_method_id',
        'total',
        'expired_time',
        'total_charged',
        'transaction_hash',
        'transaction_status_id',
        'items'
    ];

    /**
     * Relations
     */
    public $belongsTo = [ 
        'payment_method'     => 'Lulapay\Transaction\Models\PaymentMethod',
        'transaction_status' => 'Lulapay\Transaction\Models\Status',
        'merchant'           => 'Lulapay\Merchant\Models\Merchant',
        'customer'           => 'Lulapay\Transaction\Models\Customer'
    ];

    public $hasMany = [
        'transaction_details' => ['Lulapay\Transaction\Models\TransactionDetail'],
        'transaction_logs' => ['Lulapay\Transaction\Models\TransactionLog']
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'lulapay_transaction_transactions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function beforeSave() 
    {
    }
    
    public function beforeCreate() {
        $this->expired_time          =  date('Y-m-d H:i:s', strtotime('+ 30 minutes'));
        $this->transaction_hash      = Uuid::uuid4()->toString();
        $this->transaction_status_id = 1; # Set default as Pending for the first time
    }

    public function isExpired() 
    {
        $now = date('Y-m-d H:i:s');
        
        // If 30 minutes, then set as expired transaction
        if ($now >= $this->expired_time) {
            return true;
        }

        return false;
    }

    public function onCheckStatus()
    {
        
    }

    public function getMidtransTrxId()
    {
        $data = TransactionLog::whereTransactionId($this->id)->whereType('TRX')->first();

        if ($data) {
            $trx = json_decode($data->data);

            return isset($trx->transaction_id) ? $trx->transaction_id : '';
        }

        return null;
    }

    public function setStatus($status, $provider)
    {
        $mapStatusMidtrans = [
            'pending'    => 1,
            'success'    => 2,
            'settlement' => 2,
            'capture'    => 2,
            'accept'     => 2,
            'expire'     => 3,
            'cancel'     => 3,
            'deny'       => 4,
            'void'       => 4
        ];

        if ($provider == 'midtrans') {
            $status = $mapStatusMidtrans[$status] ?? '';

            if ($status && $status !== $this->transaction_status_id) {
                $this->transaction_status_id = $status;
                $this->save();
            }
        }

        return $this->transaction_status_id;
    }

    public function getStatusLabel() {
        $className = [
            1 => 'primary',
            2 => 'success',
            3 => 'warning',
            4 => 'danger',
        ];
        
        return '<span class="text-'.$className[$this->transaction_status_id].'">'.$this->transaction_status->name.'</span>';
    }

}
