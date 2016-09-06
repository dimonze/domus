<?php

/**
 * region actions.
 *
 * @package    domus
 * @subpackage region
 * @author     Garin Studio
 * @version    SVN: $Id: actions.class.php 12474 2008-10-31 10:41:27Z fabien $
 */
class regionActions extends sfActions
{
  public function executeIndex (sfWebRequest $request) {
  }

  public function executeTree (sfWebRequest $request) {
    $data = array();

    $id = $request->getParameter('id');
    $region_id = $request->getParameter('region_id');

    if (empty($id) && empty($region_id)) {
      $query = Doctrine::getTable('Region')->createQuery()->orderBy('position');
      foreach ($query->fetchArray() as $row) {
        $data[] = array(
          'data' => $row['name'],
          'attributes' => array('region_id' => $row['id'], 'type' => 'region'),
          'state' => 'closed'
        );
      }
    }
    
    elseif (!empty($region_id)) {
      $query = Doctrine::getTable('Regionnode')->createQuery()
                ->andWhere('region_id = ?', $region_id)
                ->andWhere('parent is null and list = 1');
      foreach ($query->fetchArray() as $row) {
        $data[] = array(
          'data' =>  Regionnode::formatName($row['name'], $row['socr']),
          'attributes' => array(
            'id' => $row['id'],
            'type' => 'regionnode',
            'real_name' => $row['name'] .'|'. $row['socr']),
          'state' => $row['has_children'] ? 'closed' : 'leaf'
        );
      }
    }

    elseif (!empty($id)) {
      $query = Doctrine::getTable('Regionnode')->createQuery()
                ->andWhere('parent = ?', $id);
      foreach ($query->fetchArray() as $row) {
        $data[] = array(
          'data' =>  Regionnode::formatName($row['name'], $row['socr']),
          'attributes' => array(
            'id' => $row['id'],
            'type' => 'regionnode',
            'real_name' => $row['name'] .'|'. $row['socr']),
          'state' => $row['has_children'] ? 'closed' : 'leaf'
        );
      }
    }

    return $this->renderText(json_encode($data));
  }

  public function executeRename (sfWebRequest $request) {
    $data = array();

    $id = $request->getParameter('id');
    $region_id = $request->getParameter('region_id');
    $parent_id = $request->getParameter('parent_id');
    $parent_region_id = $request->getParameter('parent_region_id');

    if (!empty($region_id)) {
      $region = Doctrine::getTable('Region')->find($region_id);
      $region->name = $request->getParameter('name');
      $region->save();
      $data = array(
          'success' => true,
          'name' => $region->name,
          'attributes' => array('region_id' => $region->id, 'type' => 'region')
        );
    }

    elseif (!empty($id)) {
      $name = explode('|', $request->getParameter('name'));
      $regionnode = Doctrine::getTable('Regionnode')->find($id);
      $regionnode->name = $name[0];
      $regionnode->socr = empty($name[1]) ? '' : $name[1];
      $regionnode->save();
      $data = array(
          'success' => true,
          'name' =>  Regionnode::formatName($regionnode->name, $regionnode->socr),
          'attributes' => array(
            'id' => $regionnode->id,
            'type' => 'regionnode',
            'real_name' => $regionnode->name .'|'. $regionnode->socr),
        );
    }

    elseif (!empty($parent_id) || !empty($parent_region_id)) {
      $name = explode('|', $request->getParameter('name'));
      $regionnode = new Regionnode();

      if (!empty($parent_id)) {
        $parent = Doctrine::getTable('Regionnode')->find($parent_id);
        $this->forward404Unless($parent);
        $regionnode->parent = $parent->id;
        $parent->has_children = true;
        $parent->save();
        $regionnode->region_id = $parent->region_id;
      }
      elseif (!empty($parent_region_id)) {
        $regionnode->region_id = $parent_region_id;
      }
      $regionnode->fromArray(array(
          'name' => $name[0],
          'socr' => empty($name[1]) ? '' : $name[1],
          'has_children' => false,
          'has_street' => false,
          'list' => !empty($parent_region_id)
        ));
      $regionnode->save();

      $data = array(
          'success' => true,
          'name' =>  Regionnode::formatName($regionnode->name, $regionnode->socr),
          'attributes' => array(
            'id' => $regionnode->id,
            'type' => 'regionnode',
            'real_name' => $regionnode->name .'|'. $regionnode->socr),
        );
    }

    return $this->renderText(json_encode($data));
  }


  public function executeMove (sfWebRequest $request) {
    $data = array();

    $parent_id = $request->getParameter('parent_id');
    $parent_region_id = $request->getParameter('parent_region_id');

    $region_id = $request->getParameter('region_id');
    $ref_region_id = $request->getParameter('ref_region_id');
    $move_type = $request->getParameter('type');

          //normalyzing
//      $q = Doctrine::getTable('Region')->createQuery()->orderBy('position');
//      $i = 1;
//      $data['i'] = array();
//      foreach ($q->execute() as $r) {
//        $r->position = $i++;
//        $r->save();
//        $data['i'][] = $r->position . ' => ' .$i;
//      }
//      return $this->renderText(json_encode($data));

    if ($move_type == 'inside') {
      $regionnode = Doctrine::getTable('Regionnode')->find($request->getParameter('id'));
      $this->forward404Unless($regionnode);

      if (!empty($parent_id)) {
        $parent = Doctrine::getTable('Regionnode')->find($parent_id);
        $this->forward404Unless($parent);

        $regionnode->parent = $parent->id;
        $regionnode->list = false;

        $parent->has_children = true;
        $parent->save();
      }
      elseif (!empty($parent_region_id)) {
        $parent = Doctrine::getTable('Regionnode')->find($regionnode->parent);

        $regionnode->region_id = $parent_region_id;
        $regionnode->parent = null;
        $regionnode->list = true;

        if ($parent) {
          $query = Doctrine::getTable('Regionnode')->createQuery()
                    ->andWhere('parent = ?', $parent->id);
          if ($query->count() <= 1) {
            $parent->has_children = false;
            $parent->save();
          }
        }
      }
      else {
        throw new Exception('No data to move');
      }
    
      $regionnode->save();
      $data = array(
          'success' => true,
          'attributes' => array(
            'id' => $regionnode->id,
            'type' => 'regionnode'
          )
        );
    }
    elseif ($move_type == 'after' || $move_type == 'before') {
      $region = Doctrine::getTable('Region')->find($region_id);
      $ref_region = Doctrine::getTable('Region')->find($ref_region_id);
      $this->forward404Unless($region && $ref_region);

      if ($move_type == 'after') {
        Doctrine::getTable('Region')->createQuery()
          ->set('position', 'position +1')
          ->where('position > ?', $ref_region->position)
          ->andWhere('position < ?', $region->position)
          ->orderBy('position')
          ->update()
          ->execute();
        Doctrine::getTable('Region')->createQuery()
          ->set('position', '?', $ref_region->position +1)
          ->where('id = ?', $region->id)
          ->update()
          ->execute();

      }
      elseif ($move_type == 'before') {
        Doctrine::getTable('Region')->createQuery()
          ->set('position', 'position +1')
          ->where('position >= ?', $ref_region->position)
          ->andWhere('position < ?', $region->position)
          ->orderBy('position')
          ->update()
          ->execute();
        Doctrine::getTable('Region')->createQuery()
          ->set('position', '?', $ref_region->position)
          ->where('id = ?', $region->id)
          ->update()
          ->execute();
      }
      else {
        throw new Exception('No data to move');
      }

      $data['success'] = true;
    }

    return $this->renderText(json_encode($data));
  }

  public function executeDelete (sfWebRequest $request) {
    $id = $request->getParameter('id');
    $region_id = $request->getParameter('region_id');

    if (!empty($id)) {
      $ids = array( array($id) );

      $query = Doctrine::getTable('Regionnode')->createQuery()->where('parent = ?', $id);
      while ($query->count()) {
        $children = array();
        foreach ($query->select('id')->fetchArray() as $row) {
          $children[] = $row['id'];
        }
        $ids[] = $children;
        $query = Doctrine::getTable('Regionnode')->createQuery()->whereIn('parent', $children);
      }
      
      foreach (array_reverse($ids) as $items) {
        Doctrine::getTable('Regionnode')->createQuery()
          ->whereIn('id', $items)
          ->delete()->execute();
      }
    }

    elseif(!empty($region_id)) {
      $ids = array();
      $query = Doctrine::getTable('Regionnode')->createQuery()
                ->where('region_id = ? and parent is null', $region_id);
      while ($query->count()) {
        $children = array();
        foreach ($query->select('id')->fetchArray() as $row) {
          $children[] = $row['id'];
        }
        $ids[] = $children;
        $query = Doctrine::getTable('Regionnode')->createQuery()->whereIn('parent', $children);
      }

      foreach (array_reverse($ids) as $items) {
        Doctrine::getTable('Regionnode')->createQuery()
          ->whereIn('id', $items)
          ->delete()->execute();
      }

      Doctrine::getTable('Region')->createQuery()
        ->where('id = ?', $region_id)
        ->delete()->execute();
    }


    return $this->renderText(json_encode(array('success' => true)));
  }

  public function executeImportnodes (sfWebRequest $request) {
    $items = preg_split('/\r?\n/', $request->getParameter('data'));

    if ($parent_id = $request->getParameter('parent_id')) {
      $parent = Doctrine::getTable('Regionnode')->find($parent_id);
      $this->forward404Unless($parent);
    }
    elseif ($parent_region_id = $request->getParameter('parent_region_id')) {
      $parent = null;
    }
    else {
      throw new Exception('Wrong data for import');
    }

    foreach ($items as $item) {
      $name = explode('|', $item);
      $name[0] = trim($name[0]);
      $name[1] = isset($name[1]) ? trim($name[1]) : '';
      
      if (empty($name[0])) {
        continue;
      }

      $regionnode = new Regionnode();

      if ($parent) {
        $regionnode->parent = $parent->id;
        $regionnode->region_id = $parent->region_id;
      }
      else {
        $regionnode->region_id = $parent_region_id;
      }

      $regionnode->fromArray(array(
          'name' => $name[0],
          'socr' => $name[1],
          'has_children' => false,
          'has_street' => false,
          'list' => !empty($parent_region_id)
        ));
      $regionnode->save();
    }
    
    if( !empty($items) && $parent instanceof Regionnode && !$parent->has_children ){
      $parent->has_children = true;
      $parent->save();
    }

    return $this->renderText(json_encode(array('success' => true)));
  }

  public function executeStreet (sfWebRequest $request) {
    $id = $request->getParameter('id');
    $region_id = $request->getParameter('region_id');
    $letter = $request->getParameter('letter');

    if (!$id) {
      $regionnode = Doctrine::getTable('Regionnode')->createQuery()
                      ->where('region_id = ? and list = 0 and socr = "г"', $region_id)
                      ->fetchOne();
      if ($regionnode) {
        $id = $regionnode->id;
      }
    }
    else {
      $regionnode = Doctrine::getTable('Regionnode')->find($id);
    }

    $this->forward404Unless($regionnode);


    if ($letter) {
      $this->streets = Doctrine::getTable('Street')->createQuery()
                       ->where('regionnode_id = ?', $id)
                       ->andWhere('name like ?', "$letter%")
                       ->execute();
    }
    else {
      $this->streets = array();
    }

    $this->letters = Doctrine::getTable('Street')->createQuery()
                       ->select('substring(name, 1, 1) as name')
                       ->distinct()
                       ->where('regionnode_id = ?', $id)
                       ->fetchArray();
    $this->regionnode = $regionnode;
  }

  public function executeImportstreet (sfWebRequest $request) {
    $regionnode = Doctrine::getTable('Regionnode')->find($request->getParameter('id'));
    $this->forward404Unless($regionnode);

    if ($request->isMethod('post')) {
      $items = preg_split('/\r?\n/', $request->getParameter('data'));
      foreach ($items as $i => $item) {
        $name = explode('|', $item);
        $name[0] = trim($name[0]);
        $name[1] = isset($name[1]) ? trim($name[1]) : '';

        if (empty($name[0])) {
          unset($items[$i]);
          continue;
        }
        

        $street = new Street();
        $street->fromArray(array(
            'name' => $name[0],
            'socr' => $name[1],
            'regionnode_id' => $regionnode->id
          ));
        $street->save();
      }

      if (count($items)) {
        $regionnode->has_street = true;
        $regionnode->save();
      }

      return $this->renderText(json_encode(array(
            'valid' => true,
            'load' => array(
              'url' => $this->generateUrl('region', array(
                  'action' => 'street',
                  'id' => $regionnode->id)),
              'rel' => 'reg'
            )
          )));
    }

    $this->regionnode = $regionnode;
  }

  public function executeUpdatestreet (sfWebRequest $request) {
    $regionnode = Doctrine::getTable('Regionnode')->find($request->getParameter('id'));
    $this->forward404Unless($regionnode);

    $data = $request->getParameter('street');

    if (!empty($data['delete'])) {
      Doctrine::getTable('Street')->createQuery()
        ->delete()
        ->where('regionnode_id = ?', $regionnode->id)
        ->andWhereIn('name', $data['delete'])
        ->execute();
    }

    if (!empty($data['name'])) {
      foreach ($data['name'] as $item => $name) {
        $name = explode('|', $name);
        $name[0] = trim($name[0]);
        $name[1] = isset($name[1]) ? trim($name[1]) : '';

        if (empty($name[0])) {
          continue;
        }

        Doctrine::getTable('Street')->createQuery()
          ->update()
          ->set('name', '?', $name[0])
          ->set('socr', '?', $name[1])
          ->where('regionnode_id = ?', $regionnode->id)
          ->andWhere('name = ?', $item)
          ->execute();
      }
    }


    return $this->renderText(json_encode(array(
          'valid' => true,
          'load' => array(
            'url' => $this->generateUrl('region', array(
                'action' => 'street',
                'id' => $regionnode->id,
                'letter' => $request->getParameter('letter'))),
            'rel' => 'reg'
          )
        )));
  }

  public function executeGmapcenter (sfWebRequest $request) {
    $this->region = Doctrine::getTable('Region')->find($request->getParameter('id'));
    $this->forward404Unless($this->region);

    if ($request->isMethod('post') && $request->isXmlHttpRequest()) {
      $this->region->fromArray(array(
        'latitude' => $request->getParameter('latitude'),
        'longitude' => $request->getParameter('longitude'),
        'zoom' => $request->getParameter('zoom'),
      ));
      $this->region->save();

      return $this->renderText(json_encode(array(
        'valid' => true,
        'load' => array(
          'url' => $this->generateUrl('region', array(
              'action' => 'gmapcenter',
              'id' => $this->region->id)),
          'rel' => 'reg'
        )
      )));
    }
  }

  public function executeSeotext(sfWebRequest $request)
  {
    $this->region = Doctrine::getTable('Region')->find($request->getParameter('id'));
    $this->forward404Unless($this->region);

    if ($request->isMethod('post') && $request->isXmlHttpRequest()) {
      $this->region->fromArray(array(
        'seotext' => $request->getParameter('seotext'),
      ));
      $this->region->save();

      return $this->renderText(json_encode(array(
        'valid' => true,
        'load' => array(
          'url' => $this->generateUrl('region', array(
            'action' => 'seotext',
            'id' => $this->region->id)),
          'rel' => 'reg'
        )
      )));
    }
  }
  
  public function executeRayontext(sfWebRequest $request)
  {
    $this->region = Doctrine::getTable('Region')->find($request->getParameter('id'));
    $this->forward404Unless($this->region);

    if ($request->isMethod('post') && $request->isXmlHttpRequest()) {
      $this->region->fromArray(array(
        'rayontext' => $request->getParameter('rayontext'),
      ));
      $this->region->save();

      return $this->renderText(json_encode(array(
        'valid' => true,
        'load' => array(
          'url' => $this->generateUrl('region', array(
            'action' => 'rayontext',
            'id' => $this->region->id)),
          'rel' => 'reg'
        )
      )));
    }
  }
  
  public function executeShossetext(sfWebRequest $request)
  {
    $this->region = Doctrine::getTable('Region')->find($request->getParameter('id'));
    $this->forward404Unless($this->region);

    if ($request->isMethod('post') && $request->isXmlHttpRequest()) {
      $this->region->fromArray(array(
        'shossetext' => $request->getParameter('shossetext'),
      ));
      $this->region->save();

      return $this->renderText(json_encode(array(
        'valid' => true,
        'load' => array(
          'url' => $this->generateUrl('region', array(
            'action' => 'shossetext',
            'id' => $this->region->id)),
          'rel' => 'reg'
        )
      )));
    }
  }
}
