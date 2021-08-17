<?php
class ControllerExtensionModuleBlogNik extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/blog_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/blog_nik');

        $this->load->model('setting/setting');

        $this->getList();
    }

    public function addArticle() {
        $this->load->language('extension/module/blog_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/blog_nik');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateArticleForm()) {
            $this->model_extension_module_blog_nik->addBlogArticle($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getFormArticle();
    }

    public function editArticle() {
        $this->load->language('extension/module/blog_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/blog_nik');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateArticleForm()) {
            $this->model_extension_module_blog_nik->editBlogArticle($this->request->get['blog_article_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getFormArticle();
    }

    public function deleteArticle() {
        $this->load->language('extension/module/blog_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/blog_nik');

        if (isset($this->request->get['blog_article_id']) && $this->validateDelete()) {
            $this->model_extension_module_blog_nik->deleteBlogArticle($this->request->get['blog_article_id']);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function saveBlogSettings() {
        $this->load->language('extension/module/blog_nik');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/blog_nik');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_extension_module_blog_nik->saveBlogSettings($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getFormBlogSetting();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'bad.title';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['addArticle'] = $this->url->link('extension/module/blog_nik/addArticle', 'user_token=' . $this->session->data['user_token'], true);
        $data['changeSettings'] = $this->url->link('extension/module/blog_nik/saveBlogSettings', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        $data['sort_article_title'] = $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . '&sort=bad.title' . $url, true);
        $data['sort_article_sort_order'] = $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . '&sort=ba.sort_order' . $url, true);

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
        );

        $results = $this->model_extension_module_blog_nik->getBlogArticles($filter_data);

        foreach ($results as $result) {
            $data['blog_articles'][] = array(
                'blog_article_id'       => $result['blog_article_id'],
                'title'                 => $result['title'],
                'sort_order'            => $result['sort_order'],
                'edit'                  => $this->url->link('extension/module/blog_nik/editArticle', 'user_token=' . $this->session->data['user_token'] . '&blog_article_id=' . $result['blog_article_id'], true),
                'delete'                => $this->url->link('extension/module/blog_nik/deleteArticle', 'user_token=' . $this->session->data['user_token'] . '&blog_article_id=' . $result['blog_article_id'], true)
            );
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/blog_list_nik', $data));
    }

    protected function getFormArticle() {
        $data['text_form'] = !isset($this->request->get['blog_article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['title'])) {
            $data['error_title'] = $this->error['title'];
        } else {
            $data['error_title'] = array();
        }

        if (isset($this->error['description'])) {
            $data['error_description'] = $this->error['description'];
        } else {
            $data['error_description'] = array();
        }

        if (isset($this->error['meta_title'])) {
            $data['error_meta_title'] = $this->error['meta_title'];
        } else {
            $data['error_meta_title'] = array();
        }

        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['blog_article_id'])) {
            $data['action'] = $this->url->link('extension/module/blog_nik/addArticle', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/module/blog_nik/editArticle', 'user_token=' . $this->session->data['user_token'] . '&blog_article_id=' . $this->request->get['blog_article_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['blog_article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $blog_article_info = $this->model_extension_module_blog_nik->getBlogArticle($this->request->get['blog_article_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['blog_article_description'])) {
            $data['blog_article_description'] = $this->request->post['blog_article_description'];
        } elseif (isset($this->request->get['blog_article_id'])) {
            $data['blog_article_description'] = $this->model_extension_module_blog_nik->getBlogArticleDescription($this->request->get['blog_article_id']);
        } else {
            $data['blog_article_description'] = array();
        }

        $this->load->model('setting/store');

        $data['stores'] = array();

        $data['stores'][] = array(
            'store_id' => 0,
            'name'     => $this->language->get('text_default')
        );

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name'     => $store['name']
            );
        }

        if (isset($this->request->post['blog_article_store'])) {
            $data['blog_article_store'] = $this->request->post['blog_article_store'];
        } elseif (isset($this->request->get['blog_article_id'])) {
            $data['blog_article_store'] = $this->model_extension_module_blog_nik->getBlogArticleStores($this->request->get['blog_article_id']);
        } else {
            $data['blog_article_store'] = array(0);
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($blog_article_info)) {
            $data['image'] = $blog_article_info['image'];
        } else {
            $data['image'] = '';
        }

        $data['thumb'] = $data['image'] ? $this->model_tool_image->resize($data['image'], 100, 100) : $this->model_tool_image->resize('no_image.png', 100, 100);
        $data['img_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($blog_article_info)) {
            $data['sort_order'] = $blog_article_info['sort_order'];
        } else {
            $data['sort_order'] = 0;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($blog_article_info)) {
            $data['status'] = $blog_article_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->request->post['blog_article_seo_url'])) {
            $data['blog_article_seo_url'] = $this->request->post['blog_article_seo_url'];
        } elseif (isset($this->request->get['blog_article_id'])) {
            $data['blog_article_seo_url'] = $this->model_extension_module_blog_nik->getBlogArticleSeoUrls($this->request->get['blog_article_id']);
        } else {
            $data['blog_article_seo_url'] = array();
        }

        if (isset($this->request->post['blog_article_layout'])) {
            $data['blog_article_layout'] = $this->request->post['blog_article_layout'];
        } elseif (isset($this->request->get['blog_article_id'])) {
            $data['blog_article_layout'] = $this->model_extension_module_blog_nik->getBlogArticleLayouts($this->request->get['blog_article_id']);
        } else {
            $data['blog_article_layout'] = array();
        }

        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/blog_article_form_nik', $data));
    }

    protected function getFormBlogSetting() {
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $help_settings = $this->model_extension_module_blog_nik->getBlogSettings();

        if (isset($this->request->post['sort'])) {
            $data['sort'] = $this->request->post['sort'];
        } elseif (!empty($help_settings)) {
            $data['sort'] = $help_settings['sort'];
        } else {
            $data['sort'] = '';
        }

        if (isset($this->request->post['order'])) {
            $data['order'] = $this->request->post['order'];
        } elseif (!empty($help_settings)) {
            $data['order'] = $help_settings['order'];
        } else {
            $data['order'] = '';
        }

        if (isset($this->request->post['limit_articles'])) {
            $data['limit_articles'] = $this->request->post['limit_articles'];
        } elseif (!empty($help_settings)) {
            $data['limit_articles'] = $help_settings['limit_articles'];
        } else {
            $data['limit_articles'] = 8;
        }

        if (isset($this->request->post['limit_related_articles'])) {
            $data['limit_related_articles'] = $this->request->post['limit_related_articles'];
        } elseif (!empty($help_settings)) {
            $data['limit_related_articles'] = $help_settings['limit_related_articles'];
        } else {
            $data['limit_related_articles'] = 8;
        }

        if (isset($this->request->post['show_tags'])) {
            $data['show_tags'] = $this->request->post['show_tags'];
        } elseif (!empty($help_settings)) {
            $data['show_tags'] = $help_settings['show_tags'];
        } else {
            $data['show_tags'] = '';
        }

        $url = '';

        $data['action'] = $this->url->link('extension/module/blog_nik/saveBlogSettings', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['cancel'] = $this->url->link('extension/module/blog_nik', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/blog_settings_form_nik', $data));
    }

    public function install() {
        if ($this->user->hasPermission('modify', 'extension/module/blog_nik')) {
            $this->load->model('extension/module/blog_nik');

            $this->model_extension_module_blog_nik->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'extension/module/blog_nik')) {
            $this->load->model('extension/module/blog_nik');

            $this->model_extension_module_blog_nik->uninstall();
        }
    }

    protected function validateArticleForm() {
        if (!$this->user->hasPermission('modify', 'extension/module/blog_nik')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['blog_article_description'] as $language_id => $value) {
            if ((utf8_strlen($value['title']) < 1) || (utf8_strlen($value['title']) > 64)) {
                $this->error['title'][$language_id] = $this->language->get('error_title');
            }

            if ((utf8_strlen($value['description']) < 1)) {
                $this->error['description'][$language_id] = $this->language->get('error_description');
            }

            if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
                $this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
            }
        }

        if ($this->request->post['blog_article_seo_url']) {
            $this->load->model('design/seo_url');

            foreach ($this->request->post['blog_article_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        if (count(array_keys($language, $keyword)) > 1) {
                            $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
                        }

                        $seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

                        foreach ($seo_urls as $seo_url) {
                            if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['blog_article_id']) || ($seo_url['query'] != 'blog_article_id=' . $this->request->get['blog_article_id']))) {
                                $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/blog_nik')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/blog_nik')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}