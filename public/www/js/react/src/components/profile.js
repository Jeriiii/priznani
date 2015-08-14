/*
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.  *
 */

/* global React *//* aby Netbeans nevyhazoval chyby kvůli nedeklarované proměnné */
module.exports = {

  /** Komponenta na profilovou fotku */
  ProfilePhoto: React.createClass({
    render: function () {
      return (
        <a className="generatedProfile" href={this.props.profileLink} title={this.props.userName}>
          <img src={this.props.profilePhotoUrl} />
        </a>
      );
    }
  })

};
