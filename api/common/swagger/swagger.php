<?php
/**
 * @user: thanatos <thanatos915@163.com>
 */

namespace api\common\swagger;

/**
 *
 *
 * @SWG\Definition(
 *   definition="ErrorDefinition",
 *   @SWG\Property(
 *      property="name",
 *      type="string",
 *      description="错误名称"
 *   ),
 *   @SWG\Property(
 *      property="message",
 *      type="string",
 *      description="错误信息"
 *   ),
 *   @SWG\Property(
 *      property="code",
 *      type="integer",
 *      description="错误码"
 *   ),
 *   @SWG\Property(
 *      property="status",
 *      type="integer",
 *      description="错误状态"
 *   ),
 *   @SWG\Property(
 *      property="type",
 *      type="string",
 *      description="错误类型"
 *   ),
 * )
 *
 * @SWG\Response(
 *      response="Error",
 *      description="错误结果",
 *      @SWG\Schema(
 *          @SWG\Property(
 *              property="success",
 *              type="boolean",
 *              description="请求结果",
 *              default=false
 *          ),
 *          @SWG\Property(
 *             property="data",
 *             ref="#/definitions/ErrorDefinition"
 *          ),
 *      )
 * )
 *
 * @SWG\Response(
 *      response="Success",
 *      description="错误结果",
 *      @SWG\Schema(
 *          @SWG\Property(
 *              property="success",
 *              type="boolean",
 *              description="请求结果",
 *              default=true
 *          ),
 *      )
 * )
 *
 */