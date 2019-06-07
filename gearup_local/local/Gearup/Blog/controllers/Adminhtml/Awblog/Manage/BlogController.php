<?php
include_once("AW/Blog/controllers/Adminhtml/Awblog/Manage/BlogController.php");

class Gearup_Blog_Adminhtml_Awblog_Manage_BlogController extends AW_Blog_Adminhtml_Awblog_Manage_BlogController
{
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('blog/blog')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $path = $model->getFeaturedImage();
            
            if(!empty($path)) {
                $model->setFeaturedImage(Mage::getBaseUrl('media').$path);            
            }

            Mage::register('blog_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('blog/posts');
            $this->displayTitle('Edit post');

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this
                ->_addContent($this->getLayout()->createBlock('blog/manage_blog_edit'))
                ->_addLeft($this->getLayout()->createBlock('blog/manage_blog_edit_tabs'))
            ;

            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Post does not exist'));
            $this->_redirect('*/*/');
        }
    }
    
    public function saveAction()
    {  
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('blog/post');
            if(!empty($_FILES['featured_image']['name'])) {
                $uploader = new Varien_File_Uploader('featured_image');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS .'awblogpic'.DS;

                $uploader->save($path, $_FILES['featured_image']['name']);

                $data['featured_image'] = 'awblogpic'.DS.$_FILES['featured_image']['name'];
            } else {
                $path = Mage::getBaseDir('media') . DS .'awblogpic'.DS;
                if(isset($data['featured_image']['delete']) && $data['featured_image']['delete'] == 1) {
                  $data['featured_image'] = NULL;
                  unlink($path.$data['featured_image']['value']);
                } else {
                  unset($data['featured_image']);
                }
            }
            if (isset($data['tags'])) {
                if ($this->getRequest()->getParam('id')) {
                    $model->load($this->getRequest()->getParam('id'));
                    $originalTags = explode(",", $model->getTags());
                } else {
                    $originalTags = array();
                }

                $tags = explode(',', $data['tags']);
                array_walk($tags, 'trim');

                foreach ($tags as $key => $tag) {
                    $tags[$key] = Mage::helper('blog')->convertSlashes($tag, 'forward');
                }
                $tags = array_unique($tags);

                $commonTags = array_intersect($tags, $originalTags);
                $removedTags = array_diff($originalTags, $commonTags);
                $addedTags = array_diff($tags, $commonTags);

                if (count($tags)) {
                    $data['tags'] = trim(implode(',', $tags));
                } else {
                    $data['tags'] = '';
                }
            }
            if (isset($data['stores'])) {
                if ($data['stores'][0] == 0) {
                    unset($data['stores']);
                    $data['stores'] = array();
                    $stores = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
                    foreach ($stores as $store) {
                        $data['stores'][] = $store->getId();
                    }
                }
            }

            $model
                ->setData($data)
                ->setId($this->getRequest()->getParam('id'))
            ;

            try {
                $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                if (isset($data['created_time']) && $data['created_time']) {
                    $dateFrom = Mage::app()->getLocale()->date($data['created_time'], $format);
                    $model->setCreatedTime(Mage::getModel('core/date')->gmtDate(null, $dateFrom->getTimestamp()));
                    $model->setUpdateTime(Mage::getModel('core/date')->gmtDate());
                } else {
                    $model->setCreatedTime(Mage::getModel('core/date')->gmtDate());
                }

                if ($this->getRequest()->getParam('user') == null) {
                    $model
                        ->setUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                        ->setUpdateUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                    ;
                } else {
                    $model
                        ->setUpdateUser(
                            Mage::getSingleton('admin/session')->getUser()->getFirstname() . " " . Mage::getSingleton(
                                'admin/session'
                            )->getUser()->getLastname()
                        )
                    ;
                }

                $model->save();

                /* recount affected tags */
                if (isset($data['stores'])) {
                    $stores = $data['stores'];
                } else {
                    $stores = array(null);
                }

                $affectedTags = array_merge($addedTags, $removedTags);

                foreach ($affectedTags as $tag) {
                    foreach ($stores as $store) {
                        if (trim($tag)) {
                            Mage::getModel('blog/tag')->loadByName($tag, $store)->refreshCount();
                        }
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('blog')->__('Post was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('blog')->__('Unable to find post to save'));
        $this->_redirect('*/*/');
    }

    
}