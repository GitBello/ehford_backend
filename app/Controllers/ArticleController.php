<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Category;
use App\Models\User;
use CodeIgniter\API\ResponseTrait;
use Config\Services;

class ArticleController extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        $art = new Articles();
        $articles = $art->findAll();
        if(is_null($art) || empty($art)){
            return $this->respond(['message' => 'ANY ARTICLE FOUND'],401);
        }
        return $this->respond($articles, 201, 'LIST OF ARTICLES');
    }

    public function saveArticle()
    {
        $rules = $this->validate ([
            'author' => [
                'rules' => 'trim|required',
                'errors' => [
                    'required' => 'Le champ auteur est obligatoire',
                ],
            ],
            'title' => [
                'rules' => 'trim|required|',
                'errors' => [
                    'required' => 'Le champ titre est obligatoire',
                ],
            ],
            'content' => [
                'rules' => 'trim|required|min_length[30]',
                'errors' => [
                    'required' => 'Le champ description est obligatoire',
                    'min_length' => 'La description doit au moins avoir 30 caracteres'
                ]
            ],
        ]);
        if(!$rules){
            return $this->respond(['message' => $this->validator->getErrors()], 400);
        }
        $data = [
            'author' => $this->request->getPost('author'),
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'category' => $this->request->getPost('category'),
        ];
        $user = new User();
        $auth = $user->where('id', $data['author'])->select('*')->first();
        if(is_null($auth) || empty($auth)){
            return $this->respond(['message' => 'UNKNOWN AUTHOR REFERENCE'], 400);
        }
        $cat = new Category();
        $categ = $cat->where('id', $data['category'])->select('*')->first();
        if(is_null($categ) || empty($categ)){
            return $this->respond(['message' => 'UNKNOWN CATEGORY REFERENCE'], 400);
        }
        $art = new Articles();
        $article = $art->insert($data);
        if(is_null($article) || empty($article)){
            return $this->respond(['message' => 'AN ERROR OCCURRED WHILE SAVING ARTICLE'], 401);
        }
        $data = $art->find($article);
        if(is_null($data) || empty($data)){
            return $this->respond(['message' => 'ANY DATA FOUND'], 409);
        }
        return $this->respond($data, 200,'ARTICLE SAVED SUCCESSFULLY');

    }
    public function clearArticle()
    {
        $art = new Articles();
        $clear = $art->clearArticle('articles');
        if(!$clear){
            return $this->respond(['message' => 'AN ERROR HAS OCCURRED WHEN TRUNCATING THE TABLE'], 409);
        }
        return $this->respond([
            'statusCode' => 200,
            'message' => 'TABLE CLEARED SUCCESSFULLY'
        ]);
    }
    public function saveCategory()
    {
        $rules = $this->validate ([
            'name' => [
                'rules' => 'trim|required|min_length[4]',
                'errors' => [
                    'required' => 'Le champ nom est obligatoire',
                    'min_length' => 'Le nom doit au moins avoir 4 caracteres'
                ],
            ],
        ]);
        if(!$rules){
            return $this->respond(['message' => $this->validator->getErrors()], 400);
        }
        $data = ['name' => $this->request->getPost('name')];
        $cat = new Category();
        $category = $cat->insert($data);
        if(is_null($category) || empty($category)){
            return $this->respond(['message' => 'AN ERROR OCCURRED WHEN SAVING CATEGORY'], 401);
        }
        $data = $cat->find($category);
        if(is_null($data) || empty($data)){
            return $this->respond(['message' => 'ANY DATA FOUND'], 401);
        }
        return $this->respond($data, 200, 'CATEGORY SAVED SUCCESSFULLY');
    }
}
