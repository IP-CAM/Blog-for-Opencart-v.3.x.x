<?php
class ControllerExtensionModuleBlogNik extends Controller {
    public function index() {
        $this->load->language('extension/module/blog_nik');
        $this->load->model('extension/module/blog_nik');
        $this->load->model('tool/image');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['faq'] = $this->url->link('extension/module/blog_nik/faq', '', true);

        $blog_settings = $this->model_extension_module_blog_nik->getBlogSettings();

        if (isset($blog_settings['limit_articles']) && !empty($blog_settings['limit_articles'])) {
            $limit = $blog_settings['limit_articles'];
        } else {
            $limit = 8;
        }

        if (isset($blog_settings['sort'])) {
            if ($blog_settings['sort'] == '1') {
                $sort = 'bad.sort_order';
            } else {
                $sort = 'ba.blog_article_id';
            }
        } else {
            $sort = 'bad.sort_order';
        }

        if (isset($blog_settings['order'])) {
            if ($blog_settings['order']) { // order = 1
                $order = 'DESC';
            } else {                       // order = 0
                $order = 'ASC';
            }
        } else {
            $order = 'ASC';
        }

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        );

        $data['articles'] = array();

        $article_total = $this->model_extension_module_blog_nik->getTotalBlogArticles();

        $articles = $this->model_extension_module_blog_nik->getBlogArticles($filter_data);

        foreach ($articles as $article) {
            if ($article['image']) {
                $thumb = $this->model_tool_image->resize($article['image'], 325, 225);
            } else {
                $thumb = $this->model_tool_image->resize('no_image.png', 325, 225);
            }

            $data['articles'][] = array(
                'blog_article_id' => $article['blog_article_id'],
                'thumb'           => $thumb,
                'title'           => $article['title'],
                'date'            => date('d', strtotime($article['date_added'])) . ' ' . $this->language->get(date('M', strtotime($article['date_added']))) . ' ' . date('Y', strtotime($article['date_added'])),
                'link'            => $this->url->link('extension/module/blog_nik/article', 'blog_article_id=' . $article['blog_article_id'])
            );
        }

        $pagination = new Pagination();
        $pagination->total = $article_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/blog_nik', '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

        // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
        if ($page == 1) {
            $this->document->addLink($this->url->link('extension/module/blog_nik', ''), 'canonical');
        } else {
            $this->document->addLink($this->url->link('extension/module/blog_nik', '&page='. $page), 'canonical');
        }

        if ($page > 1) {
            $this->document->addLink($this->url->link('extension/module/blog_nik', (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
        }

        if ($limit && ceil($article_total / $limit) > $page) {
            $this->document->addLink($this->url->link('extension/module/blog_nik', '&page='. ($page + 1)), 'next');
        }

        $data['limit'] = $limit;
        $data['page'] = $page;

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/module/blog_nik', $data));
    }

    public function article() {
        $this->load->language('extension/module/blog_nik');
        $this->load->model('extension/module/blog_nik');
        $this->load->model('tool/image');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/blog_nik')
        );

        $blog_settings = $this->model_extension_module_blog_nik->getBlogSettings();

        $data['article'] = array();

        $article_info = $this->model_extension_module_blog_nik->getBlogArticle($this->request->get['blog_article_id']);

        if ($article_info) {
            $this->document->setTitle($article_info['meta_title']);
            $this->document->setDescription($article_info['meta_description']);
            $this->document->setKeywords($article_info['meta_keyword']);

            if ($article_info['image']) {
                $thumb = $this->model_tool_image->resize($article_info['image'], 325, 225);
            } else {
                $thumb = $this->model_tool_image->resize('no_image.png', 325, 225);
            }

            $data['article'] = array(
                'blog_article_id' => $article_info['blog_article_id'],
                'title' => $article_info['title'],
                'thumb' => $thumb,
                'description' => html_entity_decode($article_info['description']),
                'date' => $article_info['date_added'],
                'link' => $this->url->link('extension/module/blog_nik/article', 'blog_article_id=' . $article_info['blog_article_id'])
            );

            if (!empty($article_info['tags'])) {
                $filter_data = array(
                    'filter_tags' => $article_info['tags'],
                    'start' => 0,
                    'limit' => isset($blog_settings['limit_related_articles']) ? $blog_settings['limit_related_articles'] : 8,
                    'blog_article_id' => $article_info['blog_article_id']
                );

                $articles = $this->model_extension_module_blog_nik->getBlogArticles($filter_data);

                $current_article_tags = explode(', ', $article_info['tags']);

                foreach ($articles as $key => $article) {
                    $tags_count = 0;
                    $article_tags = explode(', ', $article['tags']);

                    foreach ($current_article_tags as $current_article_tag) {
                        if (in_array($current_article_tag, $article_tags)) {
                            $tags_count++;
                        }
                    }

                    if ($article['image']) {
                        $thumb = $this->model_tool_image->resize($article['image'], 325, 225);
                    } else {
                        $thumb = $this->model_tool_image->resize('no_image.png', 325, 225);
                    }

                    $articles[$key]['link'] = $this->url->link('extension/module/blog_nik/article', 'blog_article_id=' . $article['blog_article_id']);
                    $articles[$key]['date'] = date('d', strtotime($article['date_added'])) . ' ' . $this->language->get(date('M', strtotime($article['date_added']))) . ' ' . date('Y', strtotime($article['date_added']));
                    $articles[$key]['thumb'] = $thumb;
                    $articles[$key]['tags_count'] = $tags_count;
                }

                $data['all_related_articles'] = $this->url->link('extension/module/blog_nik/articles', 'tags=' . $article_info['tags'] . '&blog_article_id=' . $article_info['blog_article_id'], true);

                if (isset($blog_settings['show_tags']) && $blog_settings['show_tags']) {
                    $data['article_tags'] = array();

                    foreach ($current_article_tags as $current_article_tag) {
                        $data['article_tags'][] = array(
                            'text' => $current_article_tag,
                            'link' => $this->url->link('extension/module/blog_nik/articles', 'tags=' . $current_article_tag)
                        );
                    }
                }

                $sort_order = array();

                foreach ($articles as $key => $value) {
                    $sort_order[$key] = $value['tags_count'];
                }

                array_multisort($sort_order, SORT_DESC, $articles);

                $data['related_articles'] = $articles;
            }
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/module/blog_article_nik', $data));
    }

    public function articles() {
        $this->load->language('extension/module/blog_nik');
        $this->load->model('extension/module/blog_nik');
        $this->load->model('tool/image');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['tags'])) {
            $tags = $this->request->get['tags'];
        } else {
            $tags = '';
        }

        if (isset($this->request->get['blog_article_id'])) {
            $blog_article_id = $this->request->get['blog_article_id'];
        } else {
            $blog_article_id = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/blog_nik')
        );

        if (!empty($blog_article_id)) {
            $article_info = $this->model_extension_module_blog_nik->getBlogArticle($blog_article_id);

            $data['breadcrumbs'][] = array(
                'text' => $article_info['title'],
                'href' => $this->url->link('extension/module/blog_nik/article', 'blog_article_id=' . $article_info['blog_article_id'])
            );
        }

        $blog_settings = $this->model_extension_module_blog_nik->getBlogSettings();

        if (isset($blog_settings['limit_articles']) && !empty($blog_settings['limit_articles'])) {
            $limit = $blog_settings['limit_articles'];
        } else {
            $limit = 8;
        }

        $data['article'] = array();

        $filter_data = array(
            'filter_tags'     => $tags,
            'blog_article_id' => $blog_article_id,
            'start'           => ($page - 1) * $limit,
            'limit'           => $limit
        );

        $data['articles'] = array();

        $article_total = $this->model_extension_module_blog_nik->getTotalBlogArticles($filter_data);

        $articles = $this->model_extension_module_blog_nik->getBlogArticles($filter_data);

        foreach ($articles as $article) {
            if ($article['image']) {
                $thumb = $this->model_tool_image->resize($article['image'], 325, 225);
            } else {
                $thumb = $this->model_tool_image->resize('no_image.png', 325, 225);
            }

            if ($article['description']) {
                $article['description'] = html_entity_decode($article['description']);
                $article['description'] = strip_tags($article['description']);
                $article['description'] = trim($article['description']);
                $article['description'] = substr($article['description'], 0, (int)200);
                $article['description'] = rtrim($article['description'], "!,.-");
                $article['description'] = substr($article['description'], 0, strrpos($article['description'], ' '));
                $article['description'] .= '...';
            }

            $data['articles'][] = array(
                'blog_article_id' => $article['blog_article_id'],
                'thumb'           => $thumb,
                'title'           => $article['title'],
                'description'     => $article['description'],
                'link'            => $this->url->link('extension/module/blog_nik/article', 'blog_article_id=' . $article['blog_article_id'])
            );
        }

        $pagination = new Pagination();
        $pagination->total = $article_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/blog_nik/articles', '&page={page}' . '&tags=' . $tags . ((!empty($blog_article_id)) ? '&blog_article_id=' . $blog_article_id : '' ));

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

        // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
        if ($page == 1) {
            $this->document->addLink($this->url->link('extension/module/blog_nik/articles', 'tags=' . $tags . ((!empty($blog_article_id)) ? '&blog_article_id=' . $blog_article_id : '' )), 'canonical');
        } else {
            $this->document->addLink($this->url->link('extension/module/blog_nik/articles', '&page='. $page . '&tags=' . $tags . ((!empty($blog_article_id)) ? '&blog_article_id=' . $blog_article_id : '' )), 'canonical');
        }

        if ($page > 1) {
            $this->document->addLink($this->url->link('extension/module/blog_nik/articles', (($page - 2) ? '&page='. ($page - 1) : '' ) . '&tags=' . $tags . ((!empty($blog_article_id)) ? '&blog_article_id=' . $blog_article_id : '' )), 'prev');
        }

        if ($limit && ceil($article_total / $limit) > $page) {
            $this->document->addLink($this->url->link('extension/module/blog_nik/articles', '&page='. ($page + 1) . '&tags=' . $tags . ((!empty($blog_article_id)) ? '&blog_article_id=' . $blog_article_id : '' )), 'next');
        }

        $data['limit'] = $limit;
        $data['page'] = $page;
        $data['tags'] = $tags;

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/module/blog_articles_nik', $data));
    }
}