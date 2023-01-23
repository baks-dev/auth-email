<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\Controller\Admin;

use App\Module\User\AuthEmail\Account\Repository\AllAccount\GetAllAccountInterface;

use App\System\Handler\Search\SearchDTO;
use App\System\Handler\Search\SearchForm;
use App\System\Helper\Paginator;
use App\System\Type\Locale\Locale;
use BaksDev\Core\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_ACCOUNT_EMAIL')")]
final class IndexController extends AbstractController
{
    #[Route('/admin/account/emails/{page<\d+>}', name: 'admin.index',  methods: [
      'GET',
      'POST'
    ])]
    public function index(
      Request $request,
      //GetAllAccountInterface $allAccount,
      int $page = 0,
    ) : Response
    {
	
		dd();
		
        /* Поиск */
        $search = new SearchDTO();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);
        

        /* Получаем список */
        $stmt = $allAccount->get($search);
        $query = new Paginator($page, $stmt, $request);
        

        return $this->render(
          [
            'query' => $query,
            'search' => $searchForm->createView(),
          ]);
    }

//    #[Route('/default/style', name: 'admin.user.account.index.css', methods: ['GET'], format: "css")]
//    public function css() : Response
//    {
//        return $this->assets();
//    }
    
//    #[Route('/default/app', name: 'admin.user.account.index.js', methods: ['GET'], format: "js")]
//    public function js() : Response
//    {
//        return $this->assets();
//    }
    
}