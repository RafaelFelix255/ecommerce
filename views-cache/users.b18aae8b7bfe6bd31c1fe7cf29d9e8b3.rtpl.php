<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Lista de Usuários
  </h1>
  <ol class="breadcrumb">
    <li><a href="/admin"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active"><a href="/admin/users">Usuários</a></li>
  </ol>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
  	<div class="col-md-12">
  		<div class="box box-primary">

        <div class="box-header">
          <div class="col-md-1">
            <a href="/admin/users/create" class="btn btn-success">Novo Usuário</a>
          </div>
          <div class="col-md-5">
          
          </div>
          <div class="col-md-2">
            <div class="box-tools">
              <form action="/admin/users"> 
                <div class="input-group input-group-sm">
                  <input type="text" name="name" class="form-control pull-right" placeholder="Buscar por Nome" value="<?php echo htmlspecialchars( $search['name'], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-2">
            <div class="box-tools">
              <form action="/admin/users">                                  
                <div class="input-group input-group-sm">
                  <input type="text" name="email" class="form-control pull-right" placeholder="Buscar por E-Mail" value="<?php echo htmlspecialchars( $search['email'], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-2">
            <div class="box-tools">
              <form action="/admin/users">                                  
                <div class="input-group input-group-sm">
                  <input type="text" name="login" class="form-control pull-right" placeholder="Buscar por Login" value="<?php echo htmlspecialchars( $search['login'], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
                  <div class="input-group-btn">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="box-body no-padding">
          <table class="table table-striped">
            <thead>
              <tr>
                <th style="width: 100px; text-align: center;">ID Usuário</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Login</th>
                <th style="width: 100px; text-align: center;">Admin</th>
                <th style="width: 150px; text-align: center;">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php $counter1=-1;  if( isset($users) && ( is_array($users) || $users instanceof Traversable ) && sizeof($users) ) foreach( $users as $key1 => $value1 ){ $counter1++; ?>
              <tr>
                <td style="text-align: center;"><?php echo htmlspecialchars( $value1["iduser"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                <td style="text-align: left;"><?php echo htmlspecialchars( $value1["desperson"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                <td style="text-align: left;"><?php echo htmlspecialchars( $value1["desemail"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                <td style="text-align: left;"><?php echo htmlspecialchars( $value1["deslogin"], ENT_COMPAT, 'UTF-8', FALSE ); ?></td>
                <td style="text-align: center;"><?php if( $value1["inadmin"] == 1 ){ ?>Sim<?php }else{ ?>Não<?php } ?></td>
                <td style="text-align: center;">
                  <a href="/admin/users/<?php echo htmlspecialchars( $value1["iduser"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Editar</a>
                  <a href="/admin/users/<?php echo htmlspecialchars( $value1["iduser"], ENT_COMPAT, 'UTF-8', FALSE ); ?>/delete" onclick="return confirm('Deseja realmente excluir este registro?')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Excluir</a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
          <ul class="pagination pagination-sm no-margin pull-right">
            <?php $counter1=-1;  if( isset($pages) && ( is_array($pages) || $pages instanceof Traversable ) && sizeof($pages) ) foreach( $pages as $key1 => $value1 ){ $counter1++; ?>
              <li><a href="<?php echo htmlspecialchars( $value1["href"], ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $value1["text"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
  	</div>
  </div>

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->