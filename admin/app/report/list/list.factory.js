(function () {
  'use strict';

  angular
    .module('pkg.abuse.report')
    .factory('PkgAbuseReportList', ListFactory);

  /**
   * @ngInject
   */
  function ListFactory (List, RouteHelpers, PkgAbuseReportModal) {
    var pkg = RouteHelpers.package('abuse');

    return function (isArchive) {
      var list = List(pkg.api().all('report'));

      list.bulk.add(
        isArchive ? 'Mark Unresolved' : 'Mark Resolved',
        list.patch.bind(null, {
          is_resolved: !isArchive,
        })
      );
      list.bulk.add('Assign Client/Server', assignClientServerModal);
      list.bulk.add('Bulk Reply', bulkReplyModal);

      return list;

      function assignClientServerModal(items) {
        return PkgAbuseReportModal
          .assign(items)
          .then(list.refresh.now)
          ;
      }

      function bulkReplyModal(items) {
        return PkgAbuseReportModal
          .comment(items)
          .then(list.refresh.now)
          ;
      }
    };
  }

})();
