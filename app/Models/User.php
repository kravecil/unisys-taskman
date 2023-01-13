<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Http;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mysql_auth';
    
    protected $hidden = [ 'password' ];

    protected $with = ['department'];

    protected $appends = ['fullname', 'shortname'];

    public function tasks() {
      return $this
          ->belongsToMany(Task::class, config('database.connections.mysql.database').'.task_user', 'user_id', 'task_id')
          ->using(TaskUser::class)
          ->with('document');
  }

  public function documents() {
      return $this
          ->belongsToMany(Document::class, config('database.connections.mysql.database').'.document_user', 'user_id', 'document_id')
          ->using(DocumentUser::class);
  }

  public function projects() {
      return $this
          ->hasMany(Project::class);
  }

  // public function documentsIssued() {
  //     return $this->hasMany(Document::class, 'issuer_id');
  // }

  public function relatedTasks($userTypeId) {
      return $this->tasks()
          ->wherePivot('user_type_id', $userTypeId);
  }

  public function tasksCreating() {
      $userTypeId = UserType::firstWhere('name', 'creator')?->id;
      return $this->relatedTasks($userTypeId)
          ->where('is_note', false);
  }

  public function tasksExecuting() {
      $userTypeId = UserType::firstWhere('name', 'executor')?->id;
      return $this->relatedTasks($userTypeId);
  }

  public function tasksCoexecuting() {
      $userTypeId = UserType::firstWhere('name', 'coexecutor')?->id;
      return $this->relatedTasks($userTypeId);
  }

  public function tasksControlling() {
      $userTypeId = UserType::firstWhere('name', 'controller')?->id;
      return $this->relatedTasks($userTypeId);
  }

  public function notes() {
      $userTypeId = UserType::firstWhere('name', 'creator')?->id;
      return $this->relatedTasks($userTypeId)
          ->where('is_note', true);
  }

  public function histories() {
      return $this->hasMany(History::class, config('database.connections.mysql.database').'.histories');
  }

  public function comments() {
      return $this->hasMany(Comment::class, config('database.connections.mysql.database').'.comments');
  }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions() {
        $permissions = collect();

        foreach($this->roles as $role) {
            $permissions = $permissions->concat($role->permissions);
        }

        return $permissions;
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function decrees() {
      return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'decree')->id);
    }
    
    public function ksDecrees() {
        return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'ksDecree')->id);
    }

    public function orders() {
        return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'order')->id);
    }

    public function outgoingMails() {
        return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'outgoingMail')->id);
    }

    public function mails() {
        return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'mail')->id);
    }

    public function miscdocuments() {
        return $this->documents()->where('type_id', DocumentType::firstWhere('name', 'miscdocument')->id);
    }

    public function fullname() : Attribute {
        return new Attribute(
            get: function($value) {
                return $this->lastname . ' ' .$this->firstname . ' ' . $this->middlename;
            }
        );
    }

    public function shortname() : Attribute {
        return new Attribute(
            get: function($value) {
                return $this->lastname . ' ' .
                    Str::substr(Str::ucfirst($this->firstname),0,1) . '.' .
                    Str::substr(Str::ucfirst($this->middlename),0,1) . '.';
            }
        );
    }

    public function isAdministrator() : bool {
        if ($this->permissions()->contains('name', 'administration')) return true;

        return false;
    }

    public function can($abilities, $arguments = []) {
        if ($this->isAdministrator()) return true;
        
        $abilities = collect($abilities);

        if ($this->permissions()->contains(function ($value, $key) use ($abilities){
            return $abilities->contains($value->name);
        })) return true;
        
        return false;
    }
}

class Department extends Model
{
    protected $connection = 'mysql_auth';

protected $appends = ['fullname', 'number_title' /* deprecated */];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function fullname() : Attribute {
      return new Attribute(get: function() {
        return collect([$this->number, $this->title])->join(' ');
      });
    }

    public function leaders() {
      return $this->users()->where('is_leader', true);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function childrenDepartments()
    {
        return $this->hasMany(Department::class)
            ->with('departments');
    }

    protected function numberTitle() : Attribute {
        return new Attribute(
            get: function() {
                return $this->number . ' ' . $this->title;
            }
        );
    }

    public function childrenIds() {
        $response = [$this->id];

        $rf = function ($departments) use (&$response, &$rf) {
            foreach ($departments as $department) {
                $response[] = $department->id;

                $rf($department->departments);
            }
        };

        $rf($this->childrenDepartments()->get());

        return collect($response);
    }
}

class Role extends Model {
    protected $connection = 'mysql_auth';

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

class Permission extends Model {
    protected $connection = 'mysql_auth';

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}