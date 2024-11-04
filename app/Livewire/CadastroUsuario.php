<?php

namespace App\Livewire;

use App\Jobs\ProcessarCadastroUsuario;
use App\Models\User;
use App\Services\AIServiceIntegration;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class CadastroUsuario extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $password;
    public $photo;
    public $isLoading = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'photo' => 'nullable|image|max:10120', // Limite de 1MB para o arquivo de foto
    ];

    public function cadastrar()
    {
        try {

            $this->isLoading = true;
            $this->validate([
                'photo' => 'required|image|mimes:jpeg,jpg|max:10240', // 10MB
            ]);

            $photoPath = null;
            if ($this->photo) {
                $photoPath = $this->photo->store('photos', 'public'); // Salva a foto na pasta `storage/app/public/photos`
            }

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'photo' => $photoPath,
            ]);

            //ProcessarCadastroUsuario::dispatch($user);
            $imageLink = AIServiceIntegration::generateImage($user->photo);
            $user->avatar = $imageLink;
            $user->save();

            session()->flash('message', 'Cadastro realizado com sucesso! Você pode iniciar o teste.');
            
            Auth::login($user);
            $this->isLoading = false;

            return redirect()->route('home'); // Redireciona para o teste
        } catch (\Exception $e) {
            session()->flash('error', 'O upload falhou: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.cadastro-usuario');
    }

    public static function runNodeScript()
    {
        $output = shell_exec('node ' . escapeshellarg(base_path('node-scripts/generateImage.js')) . ' 2>&1');
        $result = json_decode($output, true);

        return $result;
    }
}
