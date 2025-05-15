<?php

use Adianti\Widget\Wrapper\TDBUniqueSearch;

/**
 * CompleteFormDataGridView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-tutor
 */
class CompleteFormDataGridView extends TPage
{
    private $form;      // registration form
    private $datagrid;  // listing
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // create the form
        $this->form = new BootstrapFormBuilder('form_categories');
        $this->form->setFormTitle(_t('Manual Form/DataGrid'));
        
        // create the form fields
        $id     = new TEntry('id');
        $name   = new TEntry('name');
        $quantidade   = new TEntry('quantidade');
        $volume   = new TEntry('volume');
        //$search = new TDBUniqueSearch('search', 'samples', 'Category', 'id', 'name');
        
        
        // add the fields in the form
        $search = new TEntry('search');
        $this->form->addFields([new TLabel('Buscar por Nome')], [$search]);
        $search->setSize('40%');
        
        $this->form->addFields( [new TLabel('Name', )],  [$name] );
        $name->setSize('40%');
        $this->form->addFields( [new TLabel('quantidade', )],  [$quantidade] );
        $quantidade->setSize('40%');
        $this->form->addFields( [new TLabel('volume', )],  [$volume] );
        $volume->setSize('40%');
        
        $name->addValidation('Name', new TRequiredValidator);
        
        // define the form actions
        $this->form->addAction( 'Salvar',  new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
        $this->form->addAction('Procurar', new TAction([$this, 'onSearch']), 'fa:search blue');
        
        // id not editable
        $id->setEditable(FALSE);
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        // add the columns
        $col_id    = new TDataGridColumn('id', 'Id', 'left', '-90%');
        $col_name  = new TDataGridColumn('name', 'Name', 'center', '40%');
        $col_quantidade  = new TDataGridColumn('quantidade', 'quantidade', 'center', '40%');
        $col_volume  = new TDataGridColumn('volume', 'volume', 'center', '80%');
        
        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_name);
        $this->datagrid->addColumn($col_quantidade);
        $this->datagrid->addColumn($col_volume);
        
        $col_id->setAction( new TAction([$this, 'onReload']),   ['order' => 'id']);
        $col_name->setAction( new TAction([$this, 'onReload']), ['order' => 'name']);
        $col_quantidade->setAction( new TAction([$this, 'onReload']), ['order' => 'quantidade']);
        $col_volume->setAction( new TAction([$this, 'onReload']), ['order' => 'volume']);
        
        $action1 = new TDataGridAction([$this, 'onEdit'],   ['key' => '{id}'] );
        $action2 = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}'] );
        
        $this->datagrid->addAction($action1, 'Edit',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Delete', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // wrap objects
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid));
        
        // add the box in the page
        parent::add($vbox);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('samples');
            
            $order    = isset($param['order']) ? $param['order'] : 'id';
            // load the objects according to criteria
            $categories = Category::orderBy($order)->load();
            
            $this->datagrid->clear();
            if ($categories)
            {
                // iterate the collection of active records
                foreach ($categories as $category)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($category);
                }
            }
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
function onSave()
{
    try
    {
        TTransaction::open('samples');
        
        $this->form->validate();

        
        $data = $this->form->getData();

        // Cria manualmente o objeto Category
        $category = new Category;
        $category->id = $data->id;
        $category->name = $data->name;
        $category->quantidade = $data->quantidade;
        $category->volume = $data->volume;

        // Salva no banco
        $category->store();

        // Fecha a transação
        TTransaction::close();

        new TMessage('info', 'Registro salvo com sucesso');

        $this->onReload();
    }
    catch (Exception $e)
    {
        new TMessage('error', $e->getMessage());
        TTransaction::rollback();
    }
}

    
    /**
     * Clear form
     */
    public function onClear()
    {
        $this->form->clear( true );
    }
    
    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button
     */
    function onEdit($param)
    {
        try
        {
            if (isset($param['id']))
            {
                // get the parameter e exibe mensagem
                $key = $param['id'];
                
                // open a transaction with database 'samples'
                TTransaction::open('samples');
                
                // instantiates object Category
                $category = new Category($key);
                
                // lança os data do category no form
                $this->form->setData($category);
                
                // close the transaction
                TTransaction::close();
                $this->onReload();
            }
            else
            {
                $this->form->clear( true );
            }
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onDelete()
     * executed whenever the user clicks at the delete button
     * Ask if the user really wants to delete the record
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Do you really want to delete ?', $action);
    }
    
    /**
     * method Delete()
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            // get the parameter $key
            $key = $param['id'];
            
            // open a transaction with database 'samples'
            TTransaction::open('samples');
            
            // instantiates object Category
            $category = new Category($key);
            
            // deletes the object from the database
            $category->delete();
            
            // close the transaction
            TTransaction::close();
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action);
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method show()
     * Shows the page e seu conteúdo
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
    
    
    
    public function onSearch($param = null)
{
    try
    {
      
        TTransaction::open('samples');
        
        $data = $this->form->getData();
        
        // cria critério de busca
        $criteria = new TCriteria();
        
        if (!empty($data->search)) {
            $criteria->add(new TFilter('name', 'like', "%{$data->search}%"));
        }
        
        $repository = new TRepository('Category');
        $categories = $repository->load($criteria);
        
        $this->datagrid->clear();
        
        if ($categories)
        {
            foreach ($categories as $category)
            {
                $this->datagrid->addItem($category);
            }
        }
        
        // mantém os dados preenchidos no formulário
        $this->form->setData($data);
        
        TTransaction::close();
        
        $this->loaded = true;
    }
    catch (Exception $e)
    {
        new TMessage('error', $e->getMessage());
        TTransaction::rollback();
    }
}
}
