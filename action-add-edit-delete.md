# Exemple d'action de contrôleur add/edit/delete fusionnée

## Merci nicolas-viseux :)

```php
<?php

/** Gestion des roles
   * 
   * @Route("/Backend/role/add", name="backend_role_add")
   * @Route("/Backend/role/edit/{id<\d+>}", name="backend_role_edit")
   * @Route("/Backend/role/delete/{id<\d+>}", name="backend_role_delete")
   */
  public function form(Role $role = null, Request $request)
  {
    // On crée un objet
    if (!$role) {
      $role = new Role();
    }
    $form = $this->createForm(RoleType::class, $role);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {

      $manager = $this->getDoctrine()->getManager();
      // Ajout ou modification
      if (stristr($request->get('_route'), 'add') || stristr($request->get('_route'), 'edit')) {
        if (!$role->getId()) {
          $this->addFlash('success', 'Rôle ajouté');
        } else {
          $this->addFlash('success', 'Rôle modifié');
        }
        $manager->persist($role);
      } else {
        // Suppression
        $manager->remove($role);
        $this->addFlash('success', 'Rôle supprimé');
      }

      $manager->flush($role);

      return $this->redirectToRoute('backend_role_list');
    }

    return $this->render('backend/role/show.html.twig', [
      'formRole' => $form->createView(),
    ]);
  }
}
```

Et le template...

```twig
{% extends 'back.html.twig' %}

{% block title %}Gestion des rôles{% endblock %}

{% block body %}
  <h1>
    {% if 'add' in app.request.get('_route') %}Ajouter un rôle{% endif %}
    {% if 'edit' in app.request.get('_route') %}Modifier un rôle{% endif %}
    {% if 'delete' in app.request.get('_route') %}Supprimer un rôle{% endif %}
  </h1>

  {{ form_start(formRole) }}

    {{ form_row(formRole.name, {'attr': {'placeholder': "Libellé du rôle"}}) }}
    {{ form_row(formRole.rolestring, {'attr': {'placeholder': "Rrôle"}}) }}

      <div>
        <span>
          <button class="btn btn-success">
            {% if 'add' in app.request.get('_route') %}Ajouter le rôle{% endif %}
            {% if 'edit' in app.request.get('_route') %}Enregistrer la modification{% endif %}
            {% if 'delete' in app.request.get('_route') %}Confirmez la suppression{% endif %}
          </button>
        </span>
        <span><a class="btn btn-info" href="{{ path('backend_role_list') }}">Retour à la liste</a></span>
      </div>

  {{ form_end(formRole) }}

{% endblock %}
```