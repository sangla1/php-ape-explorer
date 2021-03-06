<?php // INDENTING (emacs/vi): -*- mode:php; tab-width:2; c-basic-offset:2; intent-tabs-mode:nil; -*- ex: set tabstop=2 expandtab:
/** PHP Application Programming Environment (PHP-APE)
 *
 * <P><B>COPYRIGHT:</B></P>
 * <PRE>
 * PHP Application Programming Environment (PHP-APE)
 * Copyright (C) 2005-2006 Cedric Dufour
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * </PRE>
 *
 * @package PHP_APE_Explorer
 * @subpackage Control
 */

/** Gallery controller
 *
 * @package PHP_APE_Explorer
 * @subpackage Control
 */
class PHP_APE_Explorer_Gallery_Controller
extends PHP_APE_Explorer_Image_Controller
{

  /*
   * CONSTRUCTORS
   ********************************************************************************/

  /** Returns a new instance of this controller for the given path
   *
   * <P><B>THROWS:</B> <SAMP>PHP_APE_Explorer_Exception</SAMP>, <SAMP>PHP_APE_Auth_AuthorizationException</SAMP>.</P>
   *
   * @param string $sPath Explorer's (relative) path (default to the REQUEST path if <SAMP>null</SAMP>)
   */
  public function __construct( $sPath = null )
  {
    // Parent constructor
    parent::__construct( $sPath );
  }


  /*
   * METHODS: HTML components
   ********************************************************************************/

  /** Returns this controller's preferences-setting bar
   *
   * @return string
   */
  public function htmlPreferencesControllerBar()
  {
    // Output
    $sOutput = null;
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignOpen();
    $sOutput .= PHP_APE_HTML_SmartTags::htmlIcon( 'S-control', null, null, null, false );
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );

    // ... Directory browser
    $bUseLeftBar = self::$roEnvironment->getUserParameter( 'php_ape.explorer.frameset.leftbar.use' );
    if( !$bUseLeftBar )
    {
      $sOutput .= PHP_APE_HTML_SmartTags::htmlLabel( self::$asResources['label.preferences.directory.browser'].':', null, null, self::$asResources['tooltip.preferences.directory.browser'], null, false, false );
      $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
      $bDirectoryBrowser_Use = self::$roEnvironment->getUserParameter( 'php_ape.explorer.directory.browser.use' );
      $sOutput .= '<INPUT TYPE="checkbox" CLASS="checkbox" ONCLICK="javascript:self.location.replace(PHP_APE_URL_addQuery(\''.self::$oDataSpace_JavaScript->encodeData( ltrim( self::$roEnvironment->makePreferencesURL( array( 'php_ape.explorer.directory.browser.use' => $bDirectoryBrowser_Use ? 0 : 1 ), null ), '?' ) ).'\',self.location.href.toString()));"'.( $bDirectoryBrowser_Use ? ' CHECKED': null ).'/>';
      $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd();
    }

    // ... Thumbnails
    $sOutput .= PHP_APE_HTML_SmartTags::htmlLabel( self::$asResources['label.preferences.image.thumbnails'].':', null, null, self::$asResources['tooltip.preferences.image.thumbnails'], null, false, false );
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
    $bUseThumbnails_inList = self::$roEnvironment->getUserParameter( 'php_ape.explorer.image.thumbnail.list.use' );
    $sOutput .= '<INPUT TYPE="checkbox" CLASS="checkbox" ONCLICK="javascript:self.location.replace(PHP_APE_URL_addQuery(\''.self::$oDataSpace_JavaScript->encodeData( ltrim( self::$roEnvironment->makePreferencesURL( array( 'php_ape.explorer.image.thumbnail.list.use' => $bUseThumbnails_inList ? 0 : 1 ), null ), '?' ) ).'\',self.location.href.toString()));"'.( $bUseThumbnails_inList ? ' CHECKED': null ).'/>';
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd();

    // ... Size
    $sOutput .= PHP_APE_HTML_SmartTags::htmlLabel( self::$asResources['label.preferences.image.detail.size'].':', null, null, self::$asResources['tooltip.preferences.image.detail.size'], null, false, false );
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
    $iDetailSize = self::$roEnvironment->getUserParameter( 'php_ape.explorer.image.size.detail' );
    $aiDetailSize_Choices = self::$roEnvironment->getStaticParameter( 'php_ape.explorer.image.size.detail.choices' );
    if( !in_array( $iDetailSize, $aiDetailSize_Choices ) ) $iDetailSize = $aiDetailSize_Choices[0];
    $sOutput .= '<SELECT ONCHANGE="javascript:self.location.replace(PHP_APE_URL_addQuery(this.value,self.location.href.toString()));">';
    foreach( $aiDetailSize_Choices as $iChoice )
      $sOutput .= '<OPTION VALUE="'.ltrim( self::$roEnvironment->makePreferencesURL( array( 'php_ape.explorer.image.size.detail' => $iChoice ), null ), '?' ).'"'.( $iChoice == $iDetailSize ? ' SELECTED' : null).'>'.$iChoice.'</OPTION>';
    $sOutput .= '</SELECT>';
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( null, false );
    $sOutput .= '<P>px</P>';

    // End
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignClose();
    return $sOutput;
  }


  /*
   * METHODS: HTML components - OVERRIDE
   ********************************************************************************/

  public function htmlTitle()
  {
    return PHP_APE_HTML_SmartTags::htmlLabel( $this->getTitle(), 'M-image', null, null, null, true, false, 'H1' );
  }


  /*
   * METHODS: actions/view - OVERRIDE
   ********************************************************************************/

  public function htmlViewOrAction()
  {
    // Output
    $sOutput = null;

    // Controller
    global $oController;
    $oController = $this;
    $rasRequestData =& $this->useRequestData();
    $amPassthruVariables = $this->getPassthruVariables();
    $sSource = $this->getSource();
    $sDestination = $this->getDestination();
    $iPrimaryKey = (integer)$this->getPrimaryKey();
    $bIsPopup = $this->isPopup();

    // Actions / Views
    switch( $sDestination )
    {

    case 'detail':
      // Database object
      $oView = new PHP_APE_Explorer_Gallery_detail();

      // Prepare
      $this->prepareDetailView( $oView );

      // Output
      $oHTML = $this->getDetailView( $oView, PHP_APE_Data_isQueryAbleResultSet::Query_Full, $amPassthruVariables );
      // ... errors
      $asErrors = $oHTML->getErrors();
      if( count( $asErrors ) )
      {
        $e = null;
        if( array_key_exists( '__GLOBAL', $asErrors ) ) $e = new PHP_APE_HTML_Data_Exception( null, $asErrors['__GLOBAL'] );
        $sOutput .= PHP_APE_HTML_Components::htmlDataException( $e, false, true );
      }
      // ... data
      $sOutput .= $this->htmlDetailControls( $iPrimaryKey );
      $oHTML->prefUseHeader( false );
      $oHTML->prefUseFooter( false );
      $oHTML->prefUseDisplayPreferences( false );
      $sOutput .= $oHTML->html();
      $sOutput .= $this->htmlDetailControls( $iPrimaryKey );
    break;

    default:
    case 'list':
      // Directory browser
      if( !self::$roEnvironment->getUserParameter( 'php_ape.explorer.frameset.leftbar.use' ) and
          self::$roEnvironment->getUserParameter( 'php_ape.explorer.directory.browser.use' ) )
      {
        $sOutput .= $this->htmlDirectoryBrowser();
        $sOutput .= PHP_APE_HTML_SmartTags::htmlSeparator();
      }

      // Database object
      $oView = new PHP_APE_Explorer_Gallery_list();

      // Prepare
      $this->prepareListView( $oView );

      // Output
      $iQueryMeta = PHP_APE_Data_isQueryAbleResultSet::Query_Full | PHP_APE_Data_isQueryAbleResultSet::Disable_DeleteAble | PHP_APE_Data_isQueryAbleResultSet::Disable_InsertAble | PHP_APE_Data_isQueryAbleResultSet::Disable_UpdateAble | PHP_APE_Data_isQueryAbleResultSet::Disable_DetailAble;
      $oHTML = $this->getListView( $oView, $iQueryMeta, $amPassthruVariables );
      // ... errors
      $asErrors = $oHTML->getErrors();
      if( count( $asErrors ) )
      {
        $e = null;
        if( array_key_exists( '__GLOBAL', $asErrors ) ) $e = new PHP_APE_HTML_Data_Exception( null, $asErrors['__GLOBAL'] );
        $sOutput .= PHP_APE_HTML_Components::htmlDataException( $e, false, true );
      }
      // ... data
      $oHTML->prefUseHeader( false );
      $oHTML->prefUseFooter( false );
      $oHTML->prefUseDisplayPreferences( false );
      $oHTML->prefUseOrderPreferences( false );
      $sOutput .= $oHTML->html();
      break;

    }

    // End
    return $sOutput;
  }

  public function htmlDetailControls( $iPrimaryKey )
  {
    // Controller
    $bIsPopup = $this->isPopup();
    $rasRequestData =& $this->useRequestData();

    // Output
    $asResources = PHP_APE_HTML_WorkSpace::useEnvironment()->loadProperties( 'PHP_APE_Explorer_Gallery_Resources' );
    $sOutput = null;

    // Controls
    $iLastPrimaryKey = array_key_exists( '__LAST', $rasRequestData ) ? (integer)$rasRequestData['__LAST'] : 0;
    $amLastPrimaryKey = $iLastPrimaryKey > 0 ? array( '__LAST' => $iLastPrimaryKey ) : null;
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignOpen();
    if( !$bIsPopup ) $sOutput .= PHP_APE_HTML_Components::htmlBack( $this->makeRequestURL( 'index.php', null, 'list' ) );
    if( $iPrimaryKey > 0 or $iPrimaryKey < $iLastPrimaryKey )
    {
      if( !$bIsPopup ) $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd();
      $sOutput .= PHP_APE_HTML_SmartTags::htmlLabel( $asResources['label.browse'].':', null, null, $asResources['tooltip.browse'], null, false, false );
      if( $iPrimaryKey > 0 )
      {
        $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlIcon( 'M-first', $this->makeRequestURL( 'index.php', null, 'detail', '0', $amLastPrimaryKey, null, $bIsPopup ), $asResources['tooltip.first'], null, true );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlIcon( 'M-previous', $this->makeRequestURL( 'index.php', null, 'detail', (string)($iPrimaryKey-1), $amLastPrimaryKey, null, $bIsPopup ), $asResources['tooltip.previous'], null, true );
      }
      if( $iPrimaryKey < $iLastPrimaryKey )
      {
        $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlIcon( 'M-next', $this->makeRequestURL( 'index.php', null, 'detail', (string)($iPrimaryKey+1), $amLastPrimaryKey, null, $bIsPopup ), $asResources['tooltip.next'], null, true );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignAdd( 'PADDING-LEFT:2px !important;', false );
        $sOutput .= PHP_APE_HTML_SmartTags::htmlIcon( 'M-last', $this->makeRequestURL( 'index.php', null, 'detail', (string)$iLastPrimaryKey, $amLastPrimaryKey, null, $bIsPopup ), $asResources['tooltip.last'], null, true );
      }
    }
    $sOutput .= PHP_APE_HTML_SmartTags::htmlAlignClose();

    // End
    return $sOutput;
  }

}
