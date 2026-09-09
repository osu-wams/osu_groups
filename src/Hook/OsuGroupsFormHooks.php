<?php

declare(strict_types=1);

namespace Drupal\osu_groups\Hook;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\redirect\RedirectRepository;

/**
 * OSU Groups Hooks.
 */
class OsuGroupsFormHooks {

  use StringTranslationTrait;

  public function __construct(
    protected readonly RedirectRepository $redirectRepository,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly RedirectDestinationInterface $redirectDestination,
    protected readonly AccountInterface $account,
  ) {}

  /**
   * Implements hook_form_BASE_FORM_ID_alter.
   */
  #[Hook('form_group_form_alter')]
  public function formGroupFormAlter(array &$form, FormStateInterface $form_state): void {
    // phpcs:disable
    /* Theme Form options if drupal.org/i/3441929 is closed
      $form['#theme'] = ['group_edit_form'];
      $form['#attached']['library'][] = 'claro/node-form';
      $form['advanced']['#type'] = 'container';
      $form['advanced']['#accordion'] = TRUE;
      $form['meta']['#type'] = 'container';
      $form['meta']['#access'] = TRUE;
      $form['revision_information']['#type'] = 'container';
      $form['revision_information']['#group'] = 'meta';
      $form['revision_information']['#attributes']['class'][] = 'entity-meta__revision';
     */
    // phpcs:enable
    $group = $form_state->getFormObject()->getEntity();

    // Ensure that we are not on a New group and that the user has the correct
    // permission.
    if (!$group->isNew() && $this->account->hasPermission('administer redirects')) {
      $gid = $group->id();
      // Find redirects to this group.
      $redirects = $this->redirectRepository->findByDestinationUri(["internal:/group/{$gid}", "entity:group/{$gid}"]);
      // Assemble the rows for the table.
      $rows = [];
      $list_builder = $this->entityTypeManager->getListBuilder('redirect');

      /** @var \Drupal\redirect\Entity\Redirect[] $redirects */
      foreach ($redirects as $redirect) {
        $row = [];
        $path = $redirect->getSourcePathWithQuery();
        $row['path'] = [
          'class' => ['redirect-table__path'],
          'data' => ['#plain_text' => $path],
          'title' => $path,
        ];
        $row['operations'] = [
          'data' => [
            '#type' => 'operations',
            '#links' => $list_builder->getOperations($redirect),
          ],
        ];
        $rows[] = $row;
      }
      // Add the list to the vertical tabs section of the form.
      $header = [
        ['class' => ['redirect-table__path'], 'data' => $this->t('From')],
        ['class' => ['redirect-table__operations'], 'data' => $this->t('Operations')],
      ];

      $form['url_redirects'] = [
        '#type' => 'details',
        '#title' => $this->t('URL redirects'),
        '#group' => 'advanced',
        '#weight' => -50,
        '#open' => FALSE,
        'table' => [
          '#type' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#empty' => $this->t('No URL redirects available.'),
          '#attributes' => ['class' => ['redirect-table']],
        ],
        '#attached' => [
          'library' => [
            'redirect/drupal.redirect.admin',
          ],
        ],
      ];

      if (!empty($rows)) {
        $form['url_redirects']['warning'] = [
          '#markup' => $this->t('Note: links open in the current window.'),
          '#prefix' => '<p>',
          '#suffix' => '</p>',
        ];
      }

      $form['url_redirects']['actions'] = [
        '#theme' => 'links',
        '#links' => [],
        '#attributes' => ['class' => ['action-links']],
      ];
      $form['url_redirects']['actions']['#links']['add'] = [
        'title' => $this->t('Add URL redirect'),
        'url' => Url::fromRoute('redirect.add', [
          'redirect' => $group->toUrl()->getInternalPath(),
          'destination' => $this->redirectDestination->get(),
        ]),
        'attributes' => [
          'class' => [
            'button',
          ],
          'target' => '_blank',
        ],
      ];
    }
  }

}
